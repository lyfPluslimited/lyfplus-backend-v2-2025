<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'subscriber_id', 'doctor_id', 'is_referral', 
    ];

    public function subscriber(){
        return $this->belongsTo(User::class,'subscriber_id', 'userID');
    }

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }
}
