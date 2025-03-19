<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Ramsey\Uuid\Uuid;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function ussdPushTP(Request $request)
    {
	$validator = Validator::make($request->all(), [
            'userId' => 'required',
            'msisdn' => 'required',
            'specialistId' => 'required',
            'amount' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json('Failed to initiate payment transaction', 422);
        }

	$username = "LyfPlus";
	$password = "YPhYtng";
	$billerNumber = "25565988558";
	$tokenUrl = "http://accessgwtest.tigo.co.tz:8080/LYFPLUS2DM-GetToken";
	$payBillRUrl = "http://accessgwtest.tigo.co.tz:8080/LYFPLUS2DM-PushBillPay";

        $authData = "username=".$username."&password=".$password."&grant_type=password";
        $authHead = array("Content-type: application/x-www-form-urlencoded","Content-length: ".strlen($authData),"Connection: close","Cache-Control: no-cache");

        $authToken = json_decode($this->requestSendTP($tokenUrl,$authHead,$authData),TRUE)['access_token'];
        while(empty($authToken)) {
            $authToken = json_decode($this->requestSendTP($tokenUrl,$authHead,$authData),TRUE)['access_token'];
        }

	$refId = Uuid::uuid4();
        $requestData = json_encode(
        array('CustomerMSISDN'=>$request->msisdn,'BillerMSISDN'=>$billerNumber,'Amount'=>$request->amount,'Remarks'=>'','ReferenceID'=>$refId->toString()),
        JSON_UNESCAPED_SLASHES);

        $requestHead = array("Content-type: application/json","Content-length: ".strlen($requestData),"Connection: close",
                             "Authorization: bearer ".$authToken, "Username: ".$username,"Password: ".$password,"Cache-Control: no-cache");

        $responseData = json_decode($this->requestSendTP($payBillRUrl,$requestHead,$requestData),TRUE);

        if($responseData['ResponseStatus']) {
	    // Log transaction to the database
	    $payment = new Payment();
	    $payment->saveTxn([
		"txnId" => $refId->toString(),
		"userId" => $request->userId,
		"specialistId" => $request->msisdn,
		"phoneNumber" => $request->msisdn,
		"amount" => $request->amount,
		"paymentService" => "TIGOPESA"
	    ]);

            return response()->json(array('status'=>TRUE,'description'=>$responseData['ResponseDescription']));
        }
	else {
            return response()->json(array('status'=>FALSE,'description'=>$responseData['ResponseDescription']));
        }
    }

    private function requestSendTP($url,$head,$xml){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $res = curl_exec($ch);

        if(curl_errno($ch)){
            return curl_error($ch);
        }
        else{
            return $res;
        }
    }
}
