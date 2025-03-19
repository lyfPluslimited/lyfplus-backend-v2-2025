<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'patient_id', 'specialist_id', 'initiation_time','is_session_active'
    ];

    public function client(){
        return $this->belongsTo(User::class,'patient_id','userID');
    }

    public function doctor(){
        return $this->belongsTo(User::class,'specialist_id', 'userID');
    }
}
