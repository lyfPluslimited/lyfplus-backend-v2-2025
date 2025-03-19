<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'subtopic';

    public $timestamps =  false;

    protected $primaryKey = 'subID';

    protected $fillable = [
        'subTopicTitle','causes', 'description', 'symptoms',
        'preventiveCureTreatment', 'timeStamp', 'topicId','image',
        'userId'
    ];

    public function author(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }

    public function topic(){
        return $this->belongsTo(Topics::class, 'topicID', 'topicID');
    }
}
