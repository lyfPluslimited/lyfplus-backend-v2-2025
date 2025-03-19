<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Swahilies;
use App\Models\SwahiliesCallback;

class SwahiliesPayController extends Controller
{
    public function paymentList(){
        return response()->json(Swahilies::all(),200);
    }

    public function makePayment(Request $request){
        $validator = Validator::make($request->all(), [
            'orderID' => 'required',
            'amount' => 'required',
            'phone' => 'required',
        ]);

        if($validator->fails()){
            return \response()->json('Missing data', 400);
        }

        Swahilies::create([
            'order_id' => $request->orderID,
            'amount' => (int)$request->amount,
            'phone' => $request->phone,
            'patient_id' => (int)$request->userID,
            'doctor_id' => (int)$request->doctorID
        ]);

        return response()->json('Payment made', 200);
    }

    public function callbackList(){
        return response()->json(SwahiliesCallback::get(), 200);
    }

    public function callback(Request $request){

        $data = $request->input();
        $data = file_get_contents("php://input");
        $cont = json_decode($data);

        SwahiliesCallback::create([
            'code' => $cont->code,
            'order_id' => $cont->transaction_details->order_id,
            'reference_id' => $cont->transaction_details->reference_id,
            'amount' => (int)$cont->transaction_details->amount
        ]);

        return \response()->json('Callback caught', 200);
    }

    public function checkOrder($orderID){

        $checkIfOrderExists = SwahiliesCallback::where('order_id', $orderID)->exists();

        if($checkIfOrderExists){
            $data = SwahiliesCallback::where('order_id', $orderID)->first();
            return response()->json($data->code, 200);
        }   

        return response()->json('Callback does not exist', 200);       
    }
}
