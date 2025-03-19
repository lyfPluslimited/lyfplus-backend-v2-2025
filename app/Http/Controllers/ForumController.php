<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Forum;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\PostLike;
use App\Models\User;
use App\Http\Controllers\IncentiveController;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(title="Forum API", version="1.0")
 * @OA\Tag(name="Forum", description="Forum management endpoints")
 */
class ForumController extends Controller
{
        /**
     * @OA\Post(
     *     path="/api/forum/save",
     *     summary="Create a new forum post",
     *     tags={"Forum"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "postCategory", "description", "title", "privacy", "tag"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="postCategory", type="string", example="General"),
     *             @OA\Property(property="description", type="string", example="This is a forum post"),
     *             @OA\Property(property="title", type="string", example="My Forum Post"),
     *             @OA\Property(property="privacy", type="string", example="public"),
     *             @OA\Property(property="tag", type="string", example="PHP,Laravel")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Post created successfully"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function createForumPost(Request $request){

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "postCategory" => "required",
            "description" => "required",
            "title" => "required",
            "privacy" => "required",
            "tag" => "required"
        ]);


        $user = User::where([
            'userID' => $request->user_id
        ])->first();

        if($user->doctorsIDverificationStatus == 'Not Verified'){
            return response()->json('Your not registered, you can\'t post', 400);
        }

        $tags = explode(',', rtrim( $request->tag, ',' ));

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/forumImages'), $filename);

            $forum  = new Forum;
            $forum->userID = $request->user_id;
            $forum->title = $request->title;
            $forum->description = $request->description;
            $forum->slug = Str::slug($request->title, '-');
            $forum->timeStamp = date("Y-m-d H:i:s");
            $forum->category = $request->postCategory;
            $forum->userPrivacy = $request->privacy;
            $forum->post_image =  "http://167.172.12.18/app/public/images/forumImages/".$filename;

            if($forum->save()){
                
                $incentiveController = new IncentiveController();
                $incentiveController->postSharing($request->user_id);

                // foreach($tag as $t){
                //     $tag = new Tag;
                //     $tag->userPostID = $forum->id;
                //     $tag->topicID = $t;
                //     $tag->save();
                // }

                return response()->json( (string)$forum->id, 200);
            }

            return response()->json('Post failed to be saved', 400);
            
        }
            $forum  = new Forum;
            $forum->userID = $request->user_id;
            $forum->title = $request->title;
            $forum->description = $request->description;
            $forum->slug = Str::slug($request->title, '-');
            $forum->timeStamp = date("Y-m-d H:i:s");
            $forum->category = $request->postCategory;
            $forum->userPrivacy = $request->privacy;
            $forum->post_image =  "N/A";

            if($forum->save()){
                foreach($tags as $t){
                    $tag = new Tag;
                    $tag->userPostID = $forum->id;
                    $tag->topicID = $t;
                    $tag->save();
                }

                return response()->json( (string)$forum->id, 200);
            }

            return response()->json('Post failed to be saved', 400);
    }

    /**
     * @OA\Get(
     *     path="/api/forum/docroom",
     *     summary="Get doctor room forum posts",
     *     tags={"Forum"},
     *     @OA\Response(
     *         response=200,
     *         description="List of doctor room forum posts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Medical Advice Needed"),
     *                 @OA\Property(property="description", type="string", example="A forum post about medical discussions."),
     *                 @OA\Property(property="slug", type="string", example="medical-advice-needed"),
     *                 @OA\Property(property="privacy", type="string", example="public"),
     *                 @OA\Property(property="image", type="string", format="url", example="https://example.com/images/forum.jpg"),
     *                 @OA\Property(property="commentsCount", type="integer", example=5),
     *                 @OA\Property(property="likesCount", type="integer", example=10),
     *                 @OA\Property(property="author", type="string", example="Dr. John Doe"),
     *                 @OA\Property(property="category", type="string", example="Health"),
     *                 @OA\Property(property="user_image", type="string", format="url", example="https://example.com/images/avatar.jpg"),
     *                 @OA\Property(property="specialization", type="string", example="Cardiology"),
     *                 @OA\Property(property="role", type="string", example="Doctor"),
     *                 @OA\Property(property="userID", type="integer", example=101),
     *                 @OA\Property(property="date", type="string", example="1623456789000000")
     *             )
     *         )
     *     )
     * )
     */
    public function getDoctorRoomForum(){
        $posts = DB::table('userpost AS p')->join('careusers AS u', 'u.userID', '=', 'p.userID')
                    ->leftjoin('userpostcomment AS c', 'c.userPostID', '=', 'p.userPostID')
                    ->leftJoin('postlike AS l','l.postID','=','p.userPostID')
                    ->orderBy('p.userPostID', 'desc')
                    ->groupBy('p.userPostID')
                    ->select(
                        'p.userPostID as id',
                        'p.title as title',
                        'p.description as description',
                        'p.slug as slug','p.userPrivacy as privacy',
                        'p.post_image as image', 
                        DB::raw('COUNT(DISTINCT c.postCommentID) as commentsCount'), 
                        DB::raw('COUNT(DISTINCT l.postlikeID) as likesCount'),
                        DB::raw('CONCAT(u.firstName," ", u.lastName) AS author'),
                        'p.category as category',
                        'u.user_image AS user_image',
                        'u.specializationID AS specialization',
                        'u.userRole AS role',
                        'u.userID AS userID',
                        DB::raw("CONCAT(UNIX_TIMESTAMP(DATE(p.timeStamp)), '000000') as date"))
                    ->get();

        return response()->json($posts, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/forum",
     *     summary="Get all forum posts",
     *     tags={"Forum"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all forum posts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Health Discussion"),
     *                 @OA\Property(property="description", type="string", example="A general forum post about health."),
     *                 @OA\Property(property="slug", type="string", example="health-discussion"),
     *                 @OA\Property(property="privacy", type="string", example="public"),
     *                 @OA\Property(property="image", type="string", format="url", example="https://example.com/images/forum.jpg"),
     *                 @OA\Property(property="commentsCount", type="integer", example=8),
     *                 @OA\Property(property="likesCount", type="integer", example=20),
     *                 @OA\Property(property="author", type="string", example="Dr. Jane Smith"),
     *                 @OA\Property(property="category", type="string", example="General Health"),
     *                 @OA\Property(property="user_image", type="string", format="url", example="https://example.com/images/avatar.jpg"),
     *                 @OA\Property(property="role", type="string", example="User"),
     *                 @OA\Property(property="userID", type="integer", example=102),
     *                 @OA\Property(property="date", type="string", example="1623456789000000")
     *             )
     *         )
     *     )
     * )
     */
    public function getAllForums(){

        $posts = DB::table('userpost AS p')->join('careusers AS u', 'u.userID', '=', 'p.userID')
                    ->leftjoin('userpostcomment AS c', 'c.userPostID', '=', 'p.userPostID')
                    ->leftJoin('postlike AS l','l.postID','=','p.userPostID')
                    ->orderBy('p.userPostID', 'desc')
                    ->groupBy('p.userPostID')
                    ->select(
                        'p.userPostID as id',
                        'p.title as title',
                        'p.description as description',
                        'p.slug as slug',
                        'p.userPrivacy as privacy',
                        'p.post_image as image', 
                        DB::raw('COUNT(DISTINCT c.postCommentID) as commentsCount'), 
                        DB::raw('COUNT(DISTINCT l.postlikeID) as likesCount'),
                        DB::raw('CONCAT(u.firstName," ", u.lastName) AS author'),
                        'p.category as category',
                        'u.user_image AS user_image',
                        'u.userRole AS role',
                        'u.userID AS userID',
                        DB::raw("CONCAT(UNIX_TIMESTAMP(DATE(p.timeStamp)), '000000') as date"))
                    ->get();

        return response()->json($posts, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/forum/forum-with-author",
     *     summary="Get all forum posts with author, comments, and likes",
     *     tags={"Forum"},
     *     @OA\Response(
     *         response=200,
     *         description="List of forum posts with author, comments, and likes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Health Discussion"),
     *                 @OA\Property(property="content", type="string", example="A general forum post about health."),
     *                 @OA\Property(property="author", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Dr. Jane Smith")
     *                 ),
     *                 @OA\Property(property="comments", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="content", type="string", example="Great post!"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="id", type="integer", example=102),
     *                             @OA\Property(property="name", type="string", example="John Doe")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="like", type="integer", example=20)
     *             )
     *         )
     *     )
     * )
     */
    public function forumWithAuthor(){
        $posts = Forum::with(['author', 'comments', 'comments.user', 'like'])->get();

        return response()->json($posts, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/forum/postForApi/{id}",
     *     summary="Get a specific forum post with the author",
     *     tags={"Forum"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the forum post",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A specific forum post with author",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Health Discussion"),
     *             @OA\Property(property="content", type="string", example="A general forum post about health."),
     *             @OA\Property(property="author", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Dr. Jane Smith")
     *             ),
     *             @OA\Property(property="userPostID", type="integer", example=1)
     *         )
     *     )
     * )
     */
    public function getPostForAPI($id){
        $forumPost = Forum::where('userPostID', $id)->with(['author'])->first();

        return response()->json($forumPost, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/forum/post/{id}",
     *     summary="Get a specific forum post with author, comments, and like count",
     *     tags={"Forum"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the forum post",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A specific forum post with author, comments, and like count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Health Discussion"),
     *             @OA\Property(property="content", type="string", example="A general forum post about health."),
     *             @OA\Property(property="author", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Dr. Jane Smith")
     *             ),
     *             @OA\Property(property="comments", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="content", type="string", example="Great post!"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=102),
     *                         @OA\Property(property="name", type="string", example="John Doe")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="like_count", type="integer", example=20)
     *         )
     *     )
     * )
     */
    public function getForumPost($id){
        $forumPost = Forum::where('userPostID', $id)->with(['author','comments'])->withCount('like')->first();

        return view('forumPost', compact('forumPost'));
    }

    public function slugGenerator(){
        $posts = Forum::get();

        foreach($posts as $post){
            Forum::where('userPostID', $post->userPostID)->update(['slug' => Str::slug($post->title, '-') ]);
        }
        echo 'slugs generated';
    }

    /**
     * @OA\Get(
     *     path="/api/forum/likes",
     *     summary="Get all forum post likes",
     *     tags={"Forum"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all forum post likes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="post_id", type="integer", example=123),
     *                 @OA\Property(property="user_id", type="integer", example=102),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-14T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-14T10:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function forumLikes(){
        $likes = PostLike::get();
        return response()->json($likes, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/forum/getForumComments/{id}",
     *     summary="Get all comments for a specific forum post",
     *     tags={"Forum"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the forum post",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments with additional metadata (likes and comment count)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="commentsCount", type="integer", example=10),
     *             @OA\Property(property="likesCount", type="integer", example=20),
     *             @OA\Property(property="userLiked", type="boolean", example=true),
     *             @OA\Property(property="comments", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="commenter", type="string", example="John Doe"),
     *                     @OA\Property(property="user_image", type="string", format="url", example="https://example.com/images/avatar.jpg"),
     *                     @OA\Property(property="role", type="string", example="User"),
     *                     @OA\Property(property="userID", type="integer", example=102),
     *                     @OA\Property(property="timePosted", type="integer", example=1623456789000),
     *                     @OA\Property(property="postCommentID", type="integer", example=1),
     *                     @OA\Property(property="userComment", type="string", example="Great post!")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function forumComments($id){
        $comments = DB::table('userpostcomment AS c')
                        ->where('userPostID','=', $id)
                        ->leftJoin('careusers AS u', 'u.userID', '=', 'c.userID')
                        ->orderBy('c.postCommentID', 'desc')
                        ->groupBy('c.postCommentID')
                        ->select(
                            DB::raw('CONCAT(u.firstName," ", u.lastName) AS commenter'),
                            'u.user_image AS user_image',
                            'u.userRole AS role',
                            'u.userID AS userID',
                            DB::raw("(UNIX_TIMESTAMP(DATE(c.timePosted))*1000) as timePosted"),
                            'c.postCommentID',
                            'c.userComment'
                        )
                        ->get();

        $commentsCount = Comment::where('userPostID', $id)->count();
        $likesCount = PostLike::where('postID', $id)->count();
        $liked = PostLike::where('postID', $id)->exists();

        return response()->json([
            'comments' => $comments,
            'commentsCount' => $commentsCount,
            'likesCount' => $likesCount,
            'userLiked' => $liked
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/forum/like/{id}",
     *     summary="Like or unlike a forum post",
     *     tags={"Forum"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the forum post",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="userID", type="integer", example=102)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Likes count and like status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="likesCount", type="integer", example=20),
     *             @OA\Property(property="userLiked", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function likePost(Request $request, $id){
        $checkIfLikeExists = PostLike::where(['postID' => $id, 'userID' => $request->userID])->exists();

        if(!$checkIfLikeExists){
            PostLike::create([
                'postID' => $id, 'userID' => $request->userID, 'timeStamp' => date("Y-m-d H:i:s")
            ]);
        } else{
            PostLike::where(['postID' => $id, 'userID' => $request->userID])->delete();
        }

        $likesCount = PostLike::where('postID', $id)->count();
        $liked = PostLike::where('postID', $id)->exists();

        return response()->json([
            'likesCount' => $likesCount,
            'userLiked' => $liked
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/forum/comment",
     *     summary="Post a comment on a forum post",
     *     tags={"Forum"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=102),
     *             @OA\Property(property="comment", type="string", example="Great discussion!"),
     *             @OA\Property(property="post_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Comment made successfully"
     *         )
     *     )
     * )
     */
    public function comment(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'comment' => 'required',
            'post_id' => 'required'
        ]);

        Comment::create([
            'userID' => $request->user_id,
            'userComment' => $request->comment,
            'userPostID' => $request->post_id,
            'timePosted' => date("Y-m-d H:i:s")
        ]);
        
        return response()->json('Comment made successfully', 201);
    }
}
