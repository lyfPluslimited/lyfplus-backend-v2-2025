<?php

namespace App\Http\Controllers;

use App\Models\SelcomModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Kreait\Firebase\Factory;

class SelcomPaymentController extends Controller
{

    public function sendToSelcomCreateOrderAPI($url, $isPost, $json, $authorization, $digest, $signedFields, $timestamp) {
        $headers = array(
        "Content-Type: application/json;charset=\"utf-8\"",
        "Accept: application/json", "Cache-Control: no-cache",
        "Authorization: SELCOM $authorization",
        "Digest-Method: HS256",
        "Digest: $digest",
        "Timestamp: $timestamp",
        "Signed-Fields: $signedFields",
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        
        if ($isPost) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_TIMEOUT, 90);
        $result = curl_exec($ch);
        
        curl_close($ch);
        $response = json_decode($result, true);
        return $response;
        
    }

    public function computeSignature($params, $signedFields, $requestTimestamp, $apiSecret){
        $fieldsOrder = explode(',', $signedFields);
        $signData = "timestamp=$requestTimestamp";
        foreach ($fieldsOrder as $key) {
        $signData .= "&$key=" . $params[$key];
        }
        // HS256 Signature Method
        return base64_encode(hash_hmac('sha256', $signData, $apiSecret, true));
    }

    public function processPayment(Request $request){
        $userName = $request->userName;
        $userEmail = $request->userEmail;
        $userPhone = $request->userPhone;
        $userAmount = $request->userAmount;
        $orderID = $request->orderID;

        $time = time();

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $apiKey = 'LYFPLUS-8Ylj0rKjYhVUkUSa';
        $apiSecret = '15423355-9j21-4O13-aT85-a78d1e158745';

        $baseURL = "https://apigwtest.selcommobile.com/v1";
        $apiEndpoint = "/checkout/create-order-minimal";
        $url = $baseURL . $apiEndpoint;

        $isPost = true;

        $req = array(
            "vendor" => "TILL60478542", // Replace with your Vendor No.
            "order_id" => "$orderID",
            "buyer_email" => "$userEmail",
            "buyer_name" => "$userName",
            "buyer_phone" => "$userPhone",
            "amount" => $userAmount,
            "currency" => "TZS",
            "webhook" => base64_encode("https://yourwebhook.co.tz/callback"),
            "no_of_items" => 1
        );

        $authorization = base64_encode($apiKey);
        $timestamp = date('c'); // 2019-02-26T09:30:46+03:00
        $signedFields  = implode(',', array_keys($req));
        $digest = app('App\Http\Controllers\SelcomPaymentController')->computeSignature($req, $signedFields, $timestamp, $apiSecret);

        $response = app('App\Http\Controllers\SelcomPaymentController')->sendToSelcomCreateOrderAPI($url, $isPost, json_encode($req), $authorization, $digest, $signedFields, $timestamp);

        return $response;
    }

    public function processOtherMobilePayment(Request $request){
        $checkIfUserExists = User::where('userID', $request->userID)->exists();

        if(!$checkIfUserExists){
            return response()->json('User does not exist', 400);
        }

        $user = User::where('userID', $request->userID)->first();

        $userName = $user->firstName.' '.$user->lastName;
        $userEmail = $user->email;
        $userAmount = $request->userAmount;
        $userPhone = ltrim($request->userPhone, '+');
        $userOrder = $request->orderID;

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $apiKey = 'LYFPLUS-8Ylj0rKjYhVUkUSa';
        $apiSecret = '15423355-9j21-4O13-aT85-a78d1e158745';

        $baseURL = "https://apigwtest.selcommobile.com/v1";
        $apiEndpoint = "/checkout/create-order-minimal";
        $url = $baseURL . $apiEndpoint;

        $isPost = true;

        $req = array(
            "vendor" => "TILL60478542", 
            "order_id" => "$userOrder",
            "buyer_email" => "$userEmail",
            "buyer_name" => "$userName",
            "buyer_phone" => "$userPhone",
            "amount" => $userAmount,
            "currency" => "TZS",
            "webhook" => "aHR0cDovL2x5ZnBsdXMuY28udHovYXBwL3B1YmxpYy9hcGkvc2VsY29tL2NhbGxiYWNr",///
            "no_of_items" => 1
        );

        $authorization = base64_encode($apiKey);
        $timestamp = date('c'); // 2019-02-26T09:30:46+03:00
        $signedFields  = implode(',', array_keys($req));

        $digest = app('App\Http\Controllers\SelcomPaymentController')->computeSignature($req, $signedFields, $timestamp, $apiSecret);

        $response = app('App\Http\Controllers\SelcomPaymentController')->sendToSelcomCreateOrderAPI($url, $isPost, json_encode($req), $authorization, $digest, $signedFields, $timestamp);

        return $response['data'][0]['payment_token'];

        // if($response['result'] == 'SUCCESS'){
        //     $selcomOrder = new SelcomPayment;
        //     $selcomOrder->order_id = $userOrder;
        //     $selcomOrder->user_id = $user->userID;
        //     $selcomOrder->specialist_id = $request->specialistID;
        //     $selcomOrder->amount = $userAmount;
        //     $selcomOrder->phonenumber_used = $userPhone;
        //     $selcomOrder->payment_status = "PENDING";
        //     $selcomOrder->payment_type = $request->paymentType;
        //     $selcomOrder->type = $request->type;
        //     $selcomOrder->save();

        //     $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        //     $database = $factory->createDatabase();

        //     //firebase functions
        //     $ref = $database->getReference('Payments/'.$user->userID.'/'.$userOrder);

        //     $ref->set([
        //        "userid" => $user->userID,
        //        "specialistid" => $request->specialistID,
        //        "orderid" => $userOrder,
        //        "transid" => "A1234",
        //        "paymentStatus" => "PENDING",
        //        "payerPhone" => $userPhone,
        //         "amount" => $userAmount,
        //         "paymentType" => $selcomOrder->payment_type,
        //         "type" => $selcomOrder->type
        //         ]);

        //     return response()->json('Payment order successfully sent', 200);
        // };

        // return response()->json('Payment order failed', 400);
    }

    public function processCardPayment(Request $request){

        $checkIfUserExists = User::where('userID', $request->userID)->exists();

        if(!$checkIfUserExists){
            return response()->json('User does not exist', 400);
        }

        $user = User::where('userID', $request->userID)->first();

        $userName = $user->firstName.' '.$user->lastName;
        $userEmail = $user->email;
        $userAmount = $request->userAmount;
        $userPhone = ltrim($request->userPhone, '+');
        $userOrder = $request->orderID;

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $apiKey = 'LYFPLUS-8Ylj0rKjYhVUkUSa';
        $apiSecret = '15423355-9j21-4O13-aT85-a78d1e158745';

        $baseURL = "https://apigwtest.selcommobile.com/v1";
        $apiEndpoint = "/checkout/create-order";
        $url = $baseURL . $apiEndpoint;

        $isPost = true;

        $req = array(
            "vendor" => "TILL60478542", 
            "order_id" => "$userOrder",
            "buyer_email" => "$userEmail",
            "buyer_name" => "$userName",
            "buyer_phone" => "$userPhone",
            "amount" => $userAmount,
            "currency" => "TZS",
            "billing" => [
                "firstname" => $user->firstName,
                "lastname" => $user->lastName,
                "address_1" => "Tanzania",
                "city" => "Dar es Salaam",
                "state_or_region" => "Dar es Salaam",
                "postcode_or_pobox" => 0000,
                "country" => "TZ",
                "phone" => $userPhone,

            ],
            "payment_methods" => "ALL,MASTERPASS,CARD,MOBILEMONEYPULL",
            "webhook" => "aHR0cDovL2x5ZnBsdXMuY28udHovYXBwL3B1YmxpYy9hcGkvc2VsY29tL2NhbGxiYWNr",///
            "no_of_items" => 1
        );

        $authorization = base64_encode($apiKey);
        $timestamp = date('c'); // 2019-02-26T09:30:46+03:00
        $signedFields  = implode(',', array_keys($req));

        $digest = app('App\Http\Controllers\SelcomPaymentController')->computeSignature($req, $signedFields, $timestamp, $apiSecret);

        $response = app('App\Http\Controllers\SelcomPaymentController')->sendToSelcomCreateOrderAPI($url, $isPost, json_encode($req), $authorization, $digest, $signedFields, $timestamp);

        return $response;

        // if($response['result'] == 'SUCCESS'){
        //     $selcomOrder = new SelcomPayment;
        //     $selcomOrder->order_id = $userOrder;
        //     $selcomOrder->user_id = $user->userID;
        //     $selcomOrder->specialist_id = $request->specialistID;
        //     $selcomOrder->amount = $userAmount;
        //     $selcomOrder->phonenumber_used = $userPhone;
        //     $selcomOrder->payment_status = "PENDING";
        //     $selcomOrder->payment_type = $request->paymentType;
        //     $selcomOrder->type = $request->type;
        //     $selcomOrder->save();

        //     $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        //     $database = $factory->createDatabase();

        //     //firebase functions
        //     $ref = $database->getReference('Payments/'.$user->userID.'/'.$userOrder);

        //     $ref->set([
        //        "userid" => $user->userID,
        //        "specialistid" => $request->specialistID,
        //        "orderid" => $userOrder,
        //        "transid" => "A1234",
        //        "paymentStatus" => "PENDING",
        //        "payerPhone" => $userPhone,
        //         "amount" => $userAmount,
        //         "paymentType" => $selcomOrder->payment_type,
        //         "type" => $selcomOrder->type
        //         ]);

        //     return response()->json('Payment order successfully sent', 200);
        // };

        // return response()->json('Payment order failed', 400);
    }

    public function processUSSDPayment(Request $request){

        $checkIfUserExists = User::where('userID', $request->userID)->exists();

        if(!$checkIfUserExists){
            return response()->json('User does not exist', 400);
        }

        $user = User::where('userID', $request->userID)->first();

        $userName = $user->firstName.' '.$user->lastName;
        $userEmail = $user->email;
        $userAmount = $request->userAmount;
        $userPhone = ltrim($request->userPhone, '+');

        $time = $request->orderID;

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $apiKey = 'LYFPLUS-8Ylj0rKjYhVUkUSa';
        $apiSecret = '15423355-9j21-4O13-aT85-a78d1e158745';

        $baseURL = "https://apigwtest.selcommobile.com/v1";
        $apiEndpoint = "/checkout/create-order-minimal";
        $url = $baseURL . $apiEndpoint;

        $isPost = true;

        $req = array(
            "vendor" => "TILL60478542", 
            "order_id" => "$time",
            "buyer_email" => "$userEmail",
            "buyer_name" => "$userName",
            "buyer_phone" => "$userPhone",
            "amount" => $userAmount,
            "currency" => "TZS",
            "webhook" => "aHR0cDovL2x5ZnBsdXMuY28udHovYXBwL3B1YmxpYy9hcGkvc2VsY29tL2NhbGxiYWNr",
            "no_of_items" => 1
        );

        $authorization = base64_encode($apiKey);
        $timestamp = date('c'); // 2019-02-26T09:30:46+03:00
        $signedFields  = implode(',', array_keys($req));
        $digest = app('App\Http\Controllers\SelcomPaymentController')->computeSignature($req, $signedFields, $timestamp, $apiSecret);

        $response = app('App\Http\Controllers\SelcomPaymentController')->sendToSelcomCreateOrderAPI($url, $isPost, json_encode($req), $authorization, $digest, $signedFields, $timestamp);

        if($response['result'] == 'SUCCESS'){

            $selcomOrder = new SelcomModel;
            $selcomOrder->order_id = $time;
            $selcomOrder->user_id = $user->userID;
            $selcomOrder->specialist_id = $request->specialistID;
            $selcomOrder->amount = $userAmount;
            $selcomOrder->phonenumber_used = $request->userPhone;
            $selcomOrder->payment_status = "PENDING";
            $selcomOrder->payment_type = $request->paymentType;
            $selcomOrder->type = $request->type;
            $selcomOrder->save();

            $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
            $database = $factory->createDatabase();

            //firebase functions
            $ref = $database->getReference('Payments/'.$user->userID.'/'.$time);

            $ref->set([
               "userid" => $user->userID,
               "specialistid" => $request->specialistID,
               "orderid" => $time,
               "transid" => "A1234",
               "paymentStatus" => "PENDING",
               "payerPhone" => $request->userPhone,
                "amount" => $userAmount,
                "paymentType" => $selcomOrder->payment_type,
                "type" => $selcomOrder->type
                ]);

            $data = array(
                "transid" => "A1234",
                "order_id" => $time,
                "msisdn" => "$userPhone"
            );

            $url2 = "https://apigwtest.selcommobile.com/v1/checkout/wallet-payment";

            $signedFields2  = implode(',', array_keys($data));

            $digest2 = app('App\Http\Controllers\SelcomPaymentController')->computeSignature($data, $signedFields2, $timestamp, $apiSecret);

            $result =  app('App\Http\Controllers\SelcomPaymentController')->sendToSelcomCreateOrderAPI($url2, $isPost, json_encode($data), $authorization, $digest2, $signedFields2, $timestamp);

            return $result;
        }

        return $response;
    }

    public function USSDcallback(Request $request){
        $selcom = SelcomModel::where('order_id', $request->order_id)->first();

        $selcom->update([
            'transid' => $request->transid,
            'reference' => $request->reference,
            'result' => $request->result,
            'resultcode' => $request->resultcode,
            'payment_status' => $request->payment_status
        ]);

        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        //firebase functions
        $ref = $database->getReference('Payments/'.$selcom->user_id.'/'.$selcom->order_id);

        $ref->update([
            'transid' =>  $request->transid,
            "resultCode" => $request->resultcode,
            "paymentStatus" => $request->payment_status,
            'result' => $request->result,
            'reference' => $request->reference,
            'sessionStart' => time()
        ]);

        return response()->json('Payment Done', 200);
    }  

    public function checkSelcomPaymentStatus(Request $request){
        $checkIfOrderExists = SelcomModel::where('order_id', $request->orderID)->exists();

        if($checkIfOrderExists){
            $order = SelcomModel::where('order_id', $request->orderID)->first();

            return response()->json([
                'selcomOrder' => $order,
                'payment_result' => $order['result']
            ], 200);
        }

        return response()->json('Order does not exist', 400);
    }

}
