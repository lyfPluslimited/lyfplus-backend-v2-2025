<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceConfirmation extends Model
{
    protected $table = 'service_confirmation';

    protected $primaryKey = 'confirmation_id';

    public function client(){
        return $this->belongsTo(User::class, 'user_id', 'userID');
    }

    public function service(){
        return $this->belongsTo(HomeService::class,'service_id', 'home_services_id');
    }
}
