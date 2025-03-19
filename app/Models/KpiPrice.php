<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiPrice extends Model
{
    protected $table = 'kpi_prices';

    protected $fillable = [
        'kpi_id', 'doctor_id', 'unit_amount'
    ];

    public function kpi(){
        return $this->belongsTo(Kpi::class);
    }

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id','userID');
    }
}
