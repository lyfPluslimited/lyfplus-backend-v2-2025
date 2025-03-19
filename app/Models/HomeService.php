<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeService extends Model
{
    protected $table = 'home_services';

    public $timestamps = false;

    protected $primaryKey = 'home_services_id';

    protected $fillable = ['service_name', 'service_image'];

    public function doctors(){
        return $this->belongsToMany(User::class, 'doctor_services', 'service_id', 'doctor_id')->withPivot('approved');
    }

    public function visits(){
        return $this->hasMany(ServiceConfirmation::class);
    }

    public function doctorRequest(){
        return $this->belongsTo(DoctorRequest::class);
    }
}
