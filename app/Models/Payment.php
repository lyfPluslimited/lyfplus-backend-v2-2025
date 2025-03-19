<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Payment extends Model
{
    protected $table = 'payment_txn';

    public function saveTxn($data) {
	DB::table($this->table)->insert([
	    "txn_id" => $data["txnId"],
	    "user_id" => $data["userId"],
	    "specialist_id" => $data["specialistId"],
	    "phone_number" => $data["phoneNumber"],
	    "amount" => $data["amount"],
	    "payment_service" => $data["paymentService"]
	]);
    }
}
