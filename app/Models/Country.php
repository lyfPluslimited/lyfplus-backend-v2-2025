<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'apps_countries';

    protected $fillable = [
        'country_code', 'country_name'
    ];

    public function regions(){
        $this->hasMany(Region::class,'state_id','id');
    }
}
