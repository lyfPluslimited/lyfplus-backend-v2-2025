<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTracking extends Model
{
    protected $table = 'daily_kpi_tracking';

    protected $fillable = [
        'doctor_id', //user id
        'amount', //default is 0
        'paid' //default is false
    ];

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id','userID');
    }

    public function incentivesDone(){
        return $this->hasMany(Incentive::class,'tracking_id','id');
    }
}
