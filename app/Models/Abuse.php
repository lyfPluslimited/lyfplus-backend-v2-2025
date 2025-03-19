<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abuse extends Model
{
    protected $table = 'postreportfeedback';

    public function reporter(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    public function post(){
        return $this->belongsTo(Forum::class, 'postID', 'userPostID');
    }
}
