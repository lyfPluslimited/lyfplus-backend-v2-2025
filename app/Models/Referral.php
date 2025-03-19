<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    public $fillable = ['patient_id', 'referral_link_id'];
}
