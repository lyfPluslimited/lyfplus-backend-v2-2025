<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSessions extends Model
{
    protected $table = 'doctor_sessions';

    public $timestamps = false;

    public function order(){
        return $this->belongsTo(SelcomModel::class, 'order_id', 'order_id');
    }
}
