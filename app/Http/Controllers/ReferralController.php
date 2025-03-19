<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\ReferralLink;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function getAllReferralCodes(){
        $referrals = ReferralLink::with('doctor')->get();
        return \response()->json($referrals, 200);
    }
    
    /**
     * createReferralLink
     *
     * @param  mixed $request
     * @param  mixed $userID
     * @return string
     */
    public function createReferralLink(Request $request, $userID){

        $referralLink = ReferralLink::firstOrCreate(
            ['doctor_id' => $userID], 
            ['link' => time() ]
        );

        return \response()->json($referralLink->link, 200);
    }
    
    /**
     * doctorSubscription
     *
     * @param  mixed $request
     * @return boolean
     */
    public function doctorSubscription(Request $request){
        $checkIfSubscriptionExists =Subscription::where([
            'doctor_id' => $request->doctorID,
            'subscriber_id' => $request->userID
        ])->exists();

        if($checkIfSubscriptionExists){
            return \response()->json(true, 200);
        }

        return \response(false, 200);
    }
     
    /**
     * subscribe
     *
     * @param  mixed $doctorID, $userID
     * @return string
     */
    public function subscribe(Request $request){

        $checkIfSubscriptionExists = Subscription::where([
            'doctor_id' => $request->doctorID,
            'subscriber_id' => $request->userID
        ])->exists();

        if($checkIfSubscriptionExists){
            Subscription::where([
                'doctor_id' => $request->doctorID,
                'subscriber_id' => $request->userID
            ])->delete();

            return \response()->json('Unsubscribed from doctor', 200);
        }

        Subscription::create([
            'doctor_id' => $request->doctorID,
            'subscriber_id' => $request->userID
        ]);

        return \response()->json('Subscribed to doctor', 200);
    }
    
    /**
     * getSubscriptions
     *
     * @param  mixed $userID
     * @return array
     */
    public function getSubscriptions($userID){

        $checkIfUserHasSubscriptions = Subscription::where('subscriber_id', $userID)->exists();

        if($checkIfUserHasSubscriptions){
            $subscriptions = Subscription::where('subscriber_id', $userID)->with('doctor')->get();
            return response()->json($subscriptions, 200);
        }

        $subscriptions = [];
        return response()->json($subscriptions, 200);  
    }

    public function subscribers(){

        $subscriptions = DB::table('subscriptions')
                            ->join('careusers as u1', 'subscriptions.subscriber_id', '=', 'u1.userID')
                            ->join('careusers as u2', 'subscriptions.doctor_id', '=', 'u2.userID')
                            ->select(
                                'u1.firstName as clientfname', 
                                'u1.lastName as clientlname', 
                                'u2.firstName as doctorfname',
                                'u2.lastName as doctorlname',
                                'subscriptions.is_referral',
                                'subscriptions.created_at',
                                'u1.timeSt')
                            ->get();

        return \response()->json($subscriptions, 200);
    }

    public function getFavoriteDoctor(Request $request, $userID){
        //send userID
        
        $checkIfReferralExists = Referral::where('patient_id', $userID)->exists();

        if($checkIfReferralExists){
            $referral = Referral::where('patient_id', $userID)->first();

            $rlink = ReferralLink::where('id', $referral->referral_link_id)->first();

            return \response()->json([
                'doctor' => $rlink->doctor
            ], 200);
        }

        return \response()->json(['message' => 'No favorite doctor'], 400);
    }

    public function addReferral(Request $request = null, $patientID, $link){

        try
            {
                $referralLink = ReferralLink::where('link', $link)->first();

                $referral = Referral::firstOrCreate(
                    ['patient_id' => $patientID],
                    ['referral_link_id' => $referralLink->id ]
                ); 

                Subscription::firstOrCreate([
                    'subscriber_id' => $patientID, 
                    'doctor_id' => $referralLink->doctor_id, 
                    'is_referral' => true
                ]);

            }
        catch(ModelNotFoundException $e)
            {
                //
            }
    }
}
