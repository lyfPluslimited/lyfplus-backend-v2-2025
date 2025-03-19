<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Swahilies extends Model
{
    protected $table = 'swahilies';

    protected $fillable = [
        'order_id',
            'amount',
            'phone',
            'patient_id',
            'doctor_id'
    ];
}
