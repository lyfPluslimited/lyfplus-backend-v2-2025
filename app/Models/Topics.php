<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topics extends Model
{
    protected $table = 'topics';

    public $timestamps = false;

    protected $primaryKey = 'topicID';

    public function articles(){
        return $this->hasMany(Article::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }
}
