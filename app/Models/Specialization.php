<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $table = 'specialization';

    public $timestamps = false;

    protected $primaryKey = 'specializationID';

    protected $fillable = [
        'specializationName', 'additionDate', 'specilizationIcon', 'specializationName_sw'
    ];

    public function user(){
        return $this->hasOne(User::class);
    }
}
