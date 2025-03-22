<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Forum",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Health Discussion"),
 *     @OA\Property(property="description", type="string", example="A general forum post about health."),
 *     @OA\Property(property="slug", type="string", example="health-discussion"),
 *     @OA\Property(property="privacy", type="string", example="public"),
 *     @OA\Property(property="image", type="string", format="url", example="https://example.com/images/forum.jpg"),
 *     @OA\Property(property="commentsCount", type="integer", example=8),
 *     @OA\Property(property="likesCount", type="integer", example=20),
 *     @OA\Property(property="author", type="string", example="Dr. Jane Smith"),
 *     @OA\Property(property="category", type="string", example="General Health"),
 *     @OA\Property(property="user_image", type="string", format="url", example="https://example.com/images/avatar.jpg"),
 *     @OA\Property(property="role", type="string", example="User"),
 *     @OA\Property(property="userID", type="integer", example=102),
 *     @OA\Property(property="date", type="string", example="1623456789000000")
 * )
 */
class Forum extends Model
{   
    protected $table = "userpost";
    protected $primaryKey = 'userPostID';

    public $timestamps = false;

    protected $fillable = [
        'slug'
    ]; 

    public function author(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    public function abuse(){
        return $this->hasMany(Abuse::class);
    }

    public function postcategory(){
        return $this->belongsTo(PostCategory::class, 'category', 'postCategoryID');
    }

    public function comments(){
        return $this->hasMany(Comment::class,'userPostID', 'userPostID');
    }

    public function like(){
        return $this->hasMany(PostLike::class, 'postID', 'userPostID');
    }

    public function likedByUser(){
        return $this->hasMany(PostLike::class, 'postID', 'userPostID');
    }
}
