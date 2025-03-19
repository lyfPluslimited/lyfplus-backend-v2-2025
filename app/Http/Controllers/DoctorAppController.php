<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Region;
use App\Models\DoctorAppointment;
use App\Models\Hospital;
use Carbon\Carbon;
use Kreait\Firebase\Factory;
use App\Models\User;

class DoctorAppController extends Controller
{
    public function getCountries(){
        return response()->json(Country::all(), 200);
    }

    public function getRegions(){
        return response()->json(Region::with('country')->get(), 200);
    }

    public function getDoctor($userID){
        
        $doctor = User::where('userID', $userID)->with([
            'consultationPayments', 'subscriptionPayments'
        ])->first();

        return \response()->json($doctor, 200);
    }

    public function getAppointments(){
        return response()->json(DoctorAppointment::all(), 200);
    }

    public function createSlots(Request $request){
        //set up firebase
        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        //db dates
        $savedDate = Carbon::parse(date("Y-m-d h:i A", strtotime("$request->date $request->start_time")));
        $endDate = Carbon::parse(date("Y-m-d h:i A", strtotime("$request->date $request->end_time")));

        //firebase dates
        $firebaseSavedDate = Carbon::parse(date("Y-m-d h:i A", strtotime("$request->date $request->start_time")));
        $firebaseEndDate = Carbon::parse(date("Y-m-d h:i A", strtotime("$request->date $request->end_time")));

        //get hospital name
        $hospitalName = Hospital::where('specializationAreaID', $request->hospital)->pluck('areaOfSpecialization')->first();

        //save to DB
        while($savedDate < $endDate){
            $slot = new DoctorAppointment;
            $slot->doctor_id = $request->doctor;
            $slot->hospital_id = $request->hospital;
            $slot->start = $savedDate->toDateTimeString();
            $slot->end = ($savedDate->addMinutes($request->period))->toDateTimeString();
            $slot->status = "open";
            $slot->timeSet = Carbon::now()->toDateTimeString();
            $slot->save();

            //add interval minutes
            $savedDate->addMinutes($request->interval);
        }

        //save to firebase
        while($firebaseSavedDate < $firebaseEndDate){
            $database->getReference("appointments/$request->doctor/$firebaseSavedDate->timestamp")->set([
                'hospitalName' => $hospitalName,
                'hospitalId' => $request->hospital,
                'startTime' => $firebaseSavedDate->timestamp,
                'endTime' => ($firebaseSavedDate->addMinutes($request->period))->timestamp,
                'status' => 'open',
               ]);

            //add interval minutes
            $firebaseSavedDate->addMinutes($request->interval);
        }

        return response()->json('done', 200);
    }
}
