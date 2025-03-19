<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    protected $table = 'incentives';

    protected $fillable = [
        'kpi_id', 'doctor_id', 'tracking_id'
    ];

    public function tracking(){
        return $this->belongsTo(KpiTracking::class,'tracking_id','id');
    }

    public function kpi(){
        return $this->belongsTo(Kpi::class,'kpi_id','id');
    }

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id','userID');
    }

}
