<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Forum;

class SecondForumController extends Controller
{
    public function getForumBasedOfUser($id){

        $posts = Forum::with('author:userID,firstName,lastName,user_image,userRole,specializationID')->withCount(
            ['likedByUser' => function ($query) use ($id) {
            $query->where('userID',$id);
        }])->withCount(['comments','like'])->orderBy('userPostID','desc')->get();

        return response()->json($posts, 200);
    }
}
