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
use OpenApi\Annotations as OA;
use App\Http\Controllers\IncentiveController;
use Illuminate\Support\Facades\DB;

class ForumController extends Controller
{
 /**
 * @OA\Get(
 *     path="/api/forum",
 *     operationId="getAllForums",
 *     tags={"Forums"},
 *     summary="Get all forums",
 *     description="Retrieves a list of all available forums",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="title", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="slug", type="string"),
 *                 @OA\Property(property="privacy", type="string"),
 *                 @OA\Property(property="image", type="string"),
 *                 @OA\Property(property="commentsCount", type="integer"),
 *                 @OA\Property(property="likesCount", type="integer"),
 *                 @OA\Property(property="author", type="string"),
 *                 @OA\Property(property="category", type="string"),
 *                 @OA\Property(property="user_image", type="string"),
 *                 @OA\Property(property="role", type="string"),
 *                 @OA\Property(property="userID", type="integer"),
 *                 @OA\Property(property="date", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No forums found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No forums found")
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

                return response()->json((string)$forum->id, 200);
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

    public function forumWithAuthor(){
        $posts = Forum::with(['author', 'comments', 'comments.user', 'like'])->get();

        return response()->json($posts, 200);
    }

    public function getPostForAPI($id){
        $forumPost = Forum::where('userPostID', $id)->with(['author'])->first();

        return response()->json($forumPost, 200);
    }

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

    public function forumLikes(){
        $likes = PostLike::get();
        return response()->json($likes, 200);
    }

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
