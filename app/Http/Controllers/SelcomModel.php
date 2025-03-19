<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelcomModel extends Model
{
    protected $table = 'selcomPayment';

    public $timestamps = false;

    protected $primaryKey = 'selcom_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['transid', 'order_id', 'reference', 'result', 'resultcode', 'payment_status', 'user_id', 'specialist_id','amount', 'phonenumber_used'];

    public function client(){
        return $this->belongsTo(User::class, 'user_id', 'userID');
    }

    public function doctor(){
        return $this->belongsTo(User::class, 'specialist_id', 'userID');
    }
}
