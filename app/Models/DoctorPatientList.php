<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorPatientList extends Model
{
    protected $table = 'doctor_patients_list';

    public $timestamps = false;

    public function doctor(){
        return $this->belongsTo(User::class, 'doctor_id', 'userID');
    }
}
