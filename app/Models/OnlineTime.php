<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineTime extends Model
{
    protected $table = 'online_times';

    protected $fillable = [
        'doctor_id', 'online_time', 'offline_time'
    ];

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }
}
 