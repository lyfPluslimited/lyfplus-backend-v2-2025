<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
