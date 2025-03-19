<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\Experience;
use Illuminate\Support\Facades\DB;

class UserHistoryController extends Controller
{
    public function getUserHistory(Request $request){
        $id = $request->specialistID;

        $history =  DB::table('userHistoryForm')
                    ->where('specialistID', $id)
                    ->join('careusers', 'userHistoryForm.userID', '=', 'careusers.userID')
                    ->get();

        return response()->json([ 'history' => $history, 'status' => 200 ], 200);
    }

    public function getPatientSpecialistHistory($userID, $specialistID){

        $data = UserHistory::where([
            'userID' => $userID,
            'specialistID' => $specialistID
        ])->get();

        return response()->json($data,200);
    }


    public function storeUserHistory(Request $request){

        date_default_timezone_set('Africa/Dar_es_Salaam');

        if($request->hasFile('diagnostic_findings_attachment')){

            $filenameForDiagnostic = time().'.'.$request->diagnostic_findings_attachment->getClientOriginalExtension();
            $request->diagnostic_findings_attachment->move(public_path('images/userHistoryImages'), $filenameForDiagnostic );

            if($request->hasFile('identity_card_attachment')){

                $filenameForID = time().'.'.$request->identity_card_attachment->getClientOriginalExtension();
                $request->filenameForID->move(public_path('images/userHistoryImages'), $filenameForID);

                UserHistory::create([
                    'consultation_for' => $request->consultation_for,
                    'the_name_consultation_for' => $request->the_name_consultation_for,
                    'reason_consultation' => $request->reason_consultation,
                    'currently_on_medication' => $request->currently_on_medication,
                    'pregnant_woman' => $request->pregnant_woman,
                    'diagnostic_findings_attachment' => "https://lyfplus.com/lyfPlus/images/userHistoryImages/$filenameForDiagnostic",
                    'identity_card_attachment' => "https://lyfplus.com/lyfPlus/images/userHistoryImages/$filenameForID",
                    'userID' => $request->userID,
                    'specialistID' => $request->specialistID,
                    'history_time' => date("Y-m-d H:i:s"),
                    'history_status' => 'not seen',
                    'medications_list' => $request->medications_list,
                    'other' => $request->other,
                    'symptoms' => $request->symptoms
                ]);

                return response()->json('History form saved', 200);
            }
           
        }            
        
    }
    
    public function getExperiencePrices(){
        // $experience = Experience::where('fee', '!=', null)->select('name', 'fee')->get();
        $experience = Experience::select('name')->get();

        return response()->json([
            'experience' => $experience,
            'status' => 200,
        ], 200);
    }
}
