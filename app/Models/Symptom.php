<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Symptom extends Model
{
    protected $table = 'symptoms';

    public $timestamps = false;

    protected $primaryKey = 'symptoms_id';
}
