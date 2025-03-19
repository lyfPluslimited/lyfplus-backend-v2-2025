<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorRequest extends Model
{
    protected $table = 'doctor_service_request';

    protected $primaryKey = 'service_request_id';

    protected $fillable = [
        'doctor_id', 'service_id'
    ];

    public function doctors(){
        return $this->hasMany(User::class, 'doctor_id', 'userID');
    }

    public function service(){
        return $this->hasOne(HomeService::class, 'home_services_id', 'service_id');
    }
}
