<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    protected $table = 'kpis';

    protected $fillable = [
        'kpi_name', 'default_unit_amount', 'default_unit_quantity'
    ];

    public function doctors(){
        return $this->belongsToMany(User::class,'incentives', 'kpi_id','doctor_id');
    }

    public function doctorPrices(){
        return $this->belongsToMany(User::class,'kpi_prices','kpi_id','doctor_id')->withPivot('unit_amount');
    }

    public function prices(){
        return $this->hasMany(KpiPrice::class);
    }
}
