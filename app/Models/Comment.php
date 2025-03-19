<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table="userpostcomment";

    protected $primaryKey = 'postCommentID';

    public $timestamps = false;

    public function user(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    public function post(){
        return $this->belongsTo(Forum::class, 'userPostID', 'userPostID');
    }

    protected $fillable = [
        'userID', 'userComment', 'userPostID', 'timePosted'
    ];
}
