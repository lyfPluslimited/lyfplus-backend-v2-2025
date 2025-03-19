<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationPayment extends Model
{
    protected $table = 'consultation_payments';

    protected $fillable = [
        'doctor_id', 'consultation_type', 'amount',
        'gpay_id'
    ]; 

    public function doctor(){
        return $this->belongsTo(User::class,'doctor_id', 'userID');
    }

}
