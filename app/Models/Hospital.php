<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    protected $table = 'specializationarea';

    public $timestamps = false;

    protected $primaryKey = 'specializationAreaID';

    protected $fillable = [
        'areaOfSpecialization', 'verificationStatus','timeStamp', 'address', 'areaOfSpecialization_image'
    ];

    public function doctorHospital(){
        return $this->hasMany(User::class);
    }
}
