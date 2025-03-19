<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationPeriod extends Model
{
    protected $table = 'consultation_period';

    protected $primaryKey = 'consultation_period_id';

    public $timestamps = false;

    protected $fillable = [
        'consultation_period_id', 'period', 'date_set', 'period_definition'
    ];
}
