<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'region';

    protected $primaryKey = 'region_id';

    protected $fillable = [
        'region_name', 'state_id'
    ];

    public function country(){
        $this->belongsTo(Country::class,'state_id', 'id');
    }
}
