<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    protected $table = 'postlike';

    public $timestamps = false;

    protected $primaryKey = 'postlikeID';

    protected $fillable = [
        'postID', 'userID', 'timeStamp'
    ];

    public function post(){
        return $this->belongsTo(Forum::class, 'postID','userPostID');
    }

    public function user(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }
}
