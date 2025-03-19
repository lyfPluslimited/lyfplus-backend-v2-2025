<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    protected $table = 'postcategory';

    public function forumPost(){
        return $this->hasMany(Forum::class);
    }
}
