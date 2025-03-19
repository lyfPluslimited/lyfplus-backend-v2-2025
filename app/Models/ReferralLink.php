<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    public $fillable = [
        'doctor_id', 'link', 'status'
    ];

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }
}
