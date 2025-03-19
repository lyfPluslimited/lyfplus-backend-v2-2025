<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ConsultationPayment;
use App\Models\SubscriptionPayment;

class SubscriptionController extends Controller
{
    public function userToConsultationScript(){
        $doctors = User::where(['userRole' => 2, 'doctorsIDverificationStatus' => 'Verified'])->get();

        foreach ($doctors as $doctor) {
            ConsultationPayment::create([
                'doctor_id' => $doctor->userID, 
                'consultation_type' => 'call',
                 'amount' => 10000,
                'gpay_id' => $doctor->call_payment_id ?? 'call'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            ConsultationPayment::create([
                'doctor_id' => $doctor->userID, 
                'consultation_type' => 'chat',
                 'amount' => 5000,
                'gpay_id' => $doctor->consultation_payment_id ?? 'consult'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            SubscriptionPayment::create([
                'doctor_id' => $doctor->userID, 
                'subscription_period' => '1 month', 
                'amount' => 100000, 
                'gpay_id' => $doctor->subscription_payment_id ?? 'subscript'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);
        }

        return response()->json('Script generated successfully',200);
    }

    public function generateConsultationPaymentScript(){
        $doctors = User::where('userRole', 2)->get();

        foreach ($doctors as $doctor) {
            ConsultationPayment::create([
                'doctor_id' => $doctor->userID, 
                'consultation_type' => 'call',
                 'amount' => 10000,
                'gpay_id' => 'call'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            ConsultationPayment::create([
                'doctor_id' => $doctor->userID, 
                'consultation_type' => 'chat',
                 'amount' => 5000,
                'gpay_id' => 'consult'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            SubscriptionPayment::create([
                'doctor_id' => $doctor->userID, 
                'subscription_period' => '1 month', 
                'amount' => 100000, 
                'gpay_id' => 'subscription1'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            SubscriptionPayment::create([
                'doctor_id' => $doctor->userID, 
                'subscription_period' => '3 months', 
                'amount' => 300000, 
                'gpay_id' => 'subscription2'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            SubscriptionPayment::create([
                'doctor_id' => $doctor->userID, 
                'subscription_period' => '6 months', 
                'amount' => 600000, 
                'gpay_id' => 'subscription3'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);

            SubscriptionPayment::create([
                'doctor_id' => $doctor->userID, 
                'subscription_period' => '12 months', 
                'amount' => 900000, 
                'gpay_id' => 'subscription4'.'_'.ltrim($doctor->phone,'+').'_'.'fee'
            ]);
        }

        return response()->json('Script generated',200);
    }
}


