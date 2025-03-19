<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $table = 'subscription_payments';

    protected $fillable = [
        'doctor_id', 'subscription_period', 'amount', 'gpay_id'
    ];

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }    
}
