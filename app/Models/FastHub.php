<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use DB;

class FastHub extends Model
{
    protected $table = "fasthub_sms";

    public $timestamps = false;

    public function sendSMS($phone, $message){
        $response = Http::post('https://secure-gw.fasthub.co.tz/fasthub/messaging/json/api', [
            "channel" => [
                'channel' => 119314,
                'password' => 'YzE2ZDUyNTMzOTI1YWY2ODllMzc1YTQ5MjA3YTEwYzBjYjU5NmMzYjE4MDExMWYyYjdmNTEzNDQ1MGE0NDZhYQ==',
            ], 
            "messages" => [
                [
                    "text" => "$message",
                    "msisdn" => "$phone",
                    "source" => "TEST"
                ]
            ]
        ]);

        // if($response->ok()){
        //     DB::table('fasthub_sms')->insert([
        //         'phone' => '+'.$phone,
        //         'status' => $response->json()['isSuccessful'],
        //         'code' => $response->json()['error_code'],
        //         'description' => $response->json()['error_description'],
        //         'message' => $message,
        //         'ref_id' => $response->json()['reference_id'],
        //         'quota' => $response->json()['sms_quota']
        //     ]);
        // }

        return $response;
    }
}