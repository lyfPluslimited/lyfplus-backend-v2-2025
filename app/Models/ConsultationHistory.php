<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationHistory extends Model
{
    protected $table = 'consultation_history';

    protected $fillable = [
        'doctor_id', 'patient_id', 'consultation_type', 'amount', 'percentage_cut'
    ];

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }

    public function patient(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }
}
