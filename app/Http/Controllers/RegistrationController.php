<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DoctorPatientList;
use App\Models\Specialization;
use App\Models\ConsultationPayment;
use App\Models\SubscriptionPayment;
use Kreait\Firebase\Factory;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\IncentiveController;

class RegistrationController extends Controller
{

    public function createUser(Request $request){

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $phoneValidator = Validator::make($request->all(), [
            'phone' => 'unique:App\User,phone',
        ]);

        if ($phoneValidator->fails()) {
            //error
            return response()->json('These credentials are already associated with another account, please use a different phone number and email to sign up', 422);
        }

        $emailValidator= Validator::make($request->all(), [
            'email' => 'unique:App\User,email'
        ]);

        if ($emailValidator->fails()) {
            //error
            return response()->json('These credentials are already associated with another account, please use a different phone number and email to sign up', 422);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:7',
            'passwordConfirm' => 'required|min:7',
            'country' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json('Failed to create user', 422);
        }

        $user = new User;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone = $request->phone;
        $user->ip_address = $request->ip();
        $user->userRole = 1;
        $user->timeSt = date("Y-m-d H:i:s");
        $user->deleted = false;

        switch($request->country){
            case "Tanzania":
                $user->country = "Tanzania";
                $user->currency = "TZS";
                $user->rate = 0.00043;
                break;
            case "Uganda":
                $user->country = "Uganda";
                $user->currency = "UGX";
                $user->rate = 0.00028;
                break;
            case "Kenya":
                $user->country = "Kenya";
                $user->currency = "KES";
                $user->rate = 0.0092;
                break;
            default:
                $user->country = "Tanzania";
                $user->currency = "TZS";
                $user->rate = 0.00043;
        }

        $user->save();

        $uniqueID = 'LyfPlusU'.$request->lastname.rand(10,20000).$user->id;

        User::where('userID', $user->id)->update(['userPromotionCode'  => $uniqueID]);

        if($request->link){
            $referralController = new ReferralController();
            $referralController->addReferral(null, $user->userID, $request->link);
        }

        return response()->json([
            'message' => 'User created successfully',
            'status' => '201',
        ],  201);

    }

    public function createUserFromInvitation(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');
        
        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();
        $auth = $factory->createAuth();

        $phoneValidator = Validator::make($request->all(), [
            'phone' => 'unique:App\User,phone',
        ]);

        if ($phoneValidator->fails()) {
            //error
            session()->flash('error','These credentials are already associated with another account, please use a different phone number and email to sign up');
            return \redirect()->back();
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'phone' => 'required',
            'password' => 'required|min:7',
            'passwordConfirm' => 'required|min:7'
        ]);

        if ($validator->fails()) {
            //error
            session()->flash('error', 'Please enter all details');
            return redirect()->back();
        }

        if($request->password != $request->passwordConfirm){
            //error
            session()->flash('error', 'Passwords do not match');
            return redirect()->back();
        }

        $checkIfPatientExists = User::where([
            'phone' => $request->phone
        ])->first();

        if(!$checkIfPatientExists){
            //INCENTIVE CONTROLLER
            $incentiveController = new IncentiveController();
            $incentiveController->patientSignupKpi($request->doctorID);
        }

        $user = new User;
        $user->firstName = $request->firstname;
        $user->lastName = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->userRole = $request->userRole;
        $user->phone = $request->phone;
        $user->userRole = 1;
        $user->timeSt = date("Y-m-d H:i:s");
        $user->deleted = false;
        
        if($user->save()){

            $uniqueID = 'LyfPlusU'.$user->lastName.rand(10,20000).$user->userID;

            User::where('userID', $user->userID)->update(['userPromotionCode' => $uniqueID]);

            $userProperties = [
                'email' => $user->email,
                'password' => $user->password,
                'uid' => (string)$user->userID
            ];
            
            $createdUser = $auth->createUser($userProperties);
            
            $ref = $database->getReference("client/$user->userID")->set([
                "email" => $user->email,
                "firstName" => $user->firstName,
                "lastName" => $user->lastName,
                "phone" => $user->phone,
                "uid" => (string)$user->userID,
                "uuid" => (string)$user->userID
               ]);
           
               return view('successfulUserRegistrationPage');
        }

        //error
        return redirect()->back();
    }

    public function showInvitationSuccessPage(){
        return view('successfulUserRegistrationPage');
    }

    public function createSpecialist(Request $request){

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $phoneValidator = Validator::make($request->all(), [
            'phone' => 'unique:App\User,phone',
        ]);

        if ($phoneValidator->fails()) {
            //error
            return response()->json(
                ['message' => 'These credentials are already associated with another account, please use a different phone number and email to sign up', 'status' => 422], 422);
        }

        $emailValidator= Validator::make($request->all(), [
            'email' => 'unique:App\User,email'
        ]);

        if ($emailValidator->fails()) {
            //error
            return response()->json(['message' => 'These credentials are already associated with another account, please use a different phone number and email to sign up', 'status' => 422], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'password' => 'required',
            'doctor_bio' => 'required',
            'regionID' => 'required',
            'experience' => 'required',
            'doctorsIDnumber' => 'required',
            'specializationID' => 'required',
            'specializationAreaID' => 'required',
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => 'Input validation failed' ], 400);
        }

        $filename = time().'.'.$request->image->getClientOriginalExtension();
        $request->image->move(public_path('images/profilepic'), $filename);

        $specialist = new User;
        $specialist->firstname = $request->firstname;
        $specialist->lastname = $request->lastname;
        $specialist->email = $request->email;
        $specialist->password = Hash::make($request->password);
        $specialist->phone = $request->phone;
        $specialist->doctor_bio = $request->doctor_bio;
        $specialist->regionID = $request->regionID;
        $specialist->experience = $request->experience;
        $specialist->doctorsIDnumber = $request->doctorsIDnumber;
        $specialist->specializationID = $request->specializationID;
        $specialist->specilizationAreaID = $request->specializationAreaID;
        $specialist->doctorsIDverificationStatus = 'Not Verified';
        $specialist->userRole = 2;
        $specialist->consultation_fee = 8000;
        $specialist->call_fee = 5000;
        $specialist->onlineStatus = 1;
        $specialist->timeSt = date("Y-m-d H:i:s");
        $specialist->lat = $request->latitude;
        $specialist->longt = $request->longitude; 
        $specialist->ip_address = $request->ip();
        $specialist->user_image = 'http://lyfplus.co.tz/app/public/images/profilepic/'.$filename;
        $specialist->deleted = false;

        if($request->source){
            $specialist->registration_source = 'web';
        } else {
            $specialist->registration_source = 'mobile';
        }
        
       if($specialist->save()){

        $specialist->update([
            'call_payment_id' => 'call'.'_'.ltrim($specialist->phone,'+').'_'.'fee',
            'consultation_payment_id' => 'consult'.'_'.ltrim($specialist->phone,'+').'_'.'fee',
            'subscription_payment_id' => 'subscription'.'_'.ltrim($specialist->phone,'+').'_'.'fee'
        ]);

        ///
        ConsultationPayment::firstOrCreate([
            'doctor_id' => $specialist->userID, 
            'consultation_type' => 'call',
             'amount' => 10000,
            'gpay_id' => 'call'.'_'.ltrim($specialist->phone,'+').'_'.'fee'
        ]);

        ConsultationPayment::firstOrCreate([
            'doctor_id' => $specialist->userID, 
            'consultation_type' => 'chat',
             'amount' => 5000,
            'gpay_id' => 'consult'.'_'.ltrim($specialist->phone,'+').'_'.'fee'
        ]);

        SubscriptionPayment::firstOrCreate([
            'doctor_id' => $specialist->userID, 
            'subscription_period' => '1 month', 
            'amount' => 100000, 
            'gpay_id' => 'subscription'.'_'.ltrim($specialist->phone,'+').'_'.'fee'
        ]);

        ////

            $message = $specialist->firstname .' '. $specialist->lastname .' has registred as a specialist and is awaiting verification.';

            //send message to William
            (new \App\Models\FastHub)->sendSMS(255713783398, $message);

            //send message to Kevin
            (new \App\Models\FastHub)->sendSMS(255782835136, $message);

            //send message to Lilith
            (new \App\Models\FastHub)->sendSMS(255768883116, $message);

            //return response with successful message, specialist details and specialist image URL
            return response()->json([
                'message' => 'Specialist saved successfully',
                'specialist' => User::where('userID', $specialist->userID)->with('specialization')->first(),
                'specializationName' => Specialization::where('specializationID', $specialist->specializationID)->pluck('specializationName'),
                'image' => $specialist->user_image,
            ],  201);

        }

        return response()->json(['status' => 400, 'message' => 'Input validation failed' ], 422);
    }

    public function sendMessageSMS(Request $request){
        $phonenumber = ltrim($request->phone, '+');
        
        (new \App\Models\FastHub)->sendSMS($phonenumber, $request->message);
    }

    public function sendMessageSMSParam($phone, $message){
        $phonenumber = ltrim($phone, '+');
        (new \App\Models\FastHub)->sendSMS($phonenumber, $message);
    }

    public function verification(Request $request){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        //firebase functions
        $ref = $database->getReference('specialist/'.$request->userID);
        
        if($ref->update([
            'verification' => 'Verified',
            'consultation_fee' => 1000,
            'call_fee' => 1000
        ])){

            $doctor = User::where('userID', $request->userID)->first();

            User::where('userID', $request->userID)->update([
                'doctorsIDverificationStatus' => 'Verified',
                'consultation_fee' => 1000,
                'call_fee' => 1000,
            ]);
                 
            $phonenumber = ltrim($doctor->phone, '+');

            $message = 'The LyfPlus team has reviewed and verified your information. Your account is now active, thank you for your interest to practice with us. Be sure to be conversant with our healthcare providers\’ terms and conditions and terms and conditions for consultation and prescription. For any enquiries please contact us through partnerdoctors@lyfplus.com or call +255745247261';

            //my own number for testing
            (new \App\Models\FastHub)->sendSMS($phonenumber, $message);

            return response()->json([
                'message' => 'User verified successfully'
            ],200);
        }

        return response()->json([
            'message' => 'User could not be verified'
        ],400);
    }

    public function unverify(Request $request){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        $ref = $database->getReference('specialist/'.$request->userID);
        
        if($ref->update([
            'verification' => 'Not Verified'
        ])){

            User::where('userID', $request->userID)->update([
                'doctorsIDverificationStatus' => 'Not Verified'
            ]);

            $specialist = User::where('userID', $request->userID)->first();

            $phonenumber = ltrim($specialist->phone, '+');

            $message = 'Your LyfPlus account is frozen. The information you provided is either insufficient or you violated the terms of practice. Your account is now INACTIVE. Please contact us through partnerdoctors@lyfplus.com or call +255 745247261.';

            //my own number for testing
            (new \App\Models\FastHub)->sendSMS($phonenumber, $message);

            return response()->json([
                'message' => 'User unverified successfully',
            ],200);  
            
        }
        
        return response()->json([
                'message' => 'User unverification failed',
                'status' => 400
            ], 400);

    }

    //update doctor profile
    public function updateDoctorProfile(Request $request){

        if(User::where('userID', $request->userID)->update([
            'gender' => $request->gender,
            'dateOfBirth' => $request->dob,
            'address' => $request->street,
            'height' => $request->height,
            'weight' => $request->weight,
            'allergy' => $request->allergies,
            'blood_group' => $request->bloodGroup,
        ])){
            return response()->json('Doctor updated', 200);
        };
        return response()->json('Doctor cannot be updated', 400);
    }

    //update doctor profile image
    public function updateDoctorImage(Request $request){

        $filename = time().'.'.$request->image->getClientOriginalExtension();
        $request->image->move(public_path('images/profilepic'), $filename);

        if(User::where('userID', $request->userID)->update([
            'user_image' => 'http://lyfplus.co.tz/app/public/images/profilepic/'.$filename
        ])){
            return response()->json([
                'status' => 200,
                'image' => 'http://lyfplus.co.tz/app/public/images/profilepic/'.$filename
            ], 200);
        }

        return response()->json('Failed to update doctor profile image', 400);
    }

    public function registerPatient(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'phone' => 'required|unique:App\DoctorPatientList,phone',
            'specializationID' => 'required',
            'invitationCode' => 'required|unique:App\DoctorPatientList,invitation_hash',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Failed to register patient',
                'status' => 400
            ], 400);
        }

        $list = new DoctorPatientList();
        $list->first_name = $request->firstName;
        $list->last_name = $request->lastName;
        $list->phone = $request->phone;
        $list->email = $request->email;
        $list->residence = $request->residence;
        $list->patient_condition_list = $request->medicalConditions;
        $list->doctor_id = $request->specializationID;
        $list->date_added = date("Y-m-d H:i:s");
        $list->invitation_hash = $request->invitationCode;
        $list->dob = $request->birthday;

        //INCENTIVE CONTROLLER
        $incentiveController = new IncentiveController();
        $incentiveController->patientInvitationKpi($request->specializationID);

        if($list->save()){

            $doctor = User::where('userID', $list->doctor_id)->first();

            $phonenumber = ltrim($list->phone, '+');

            $message = "Dr. $doctor->firstName $doctor->lastName is inviting you to consult him online on LyfPlus mobile app via this link: http://lyfplus.co.tz/app/public/invitation/$list->invitation_hash. LyfPlus allows you to consult various specialist doctors online fast and convenient. Download LyfPlus mobile app from Google PlayStore now and have your doctor wherever you go.";

            app('App\Http\Controllers\RegistrationController')->sendMessageSMSParam($list->phone, $message);

            return response()->json([
                'message' => 'Patient details filled successfully',
                'status' => 201,
            ], 201);
        }

        return response()->json([
            'message' => 'Failed to register patient',
            'status' => 422
        ], 422);
    }

    public function invitationConfirmation(Request $request, $hash){
        $patient = DoctorPatientList::where('invitation_hash', $hash)->with('doctor')->first();
        return view('invitation', compact('patient'));
    }

    public function getUserObject(Request $request){
        $user = User::where('userID', $request->id)->first();

        return response()->json([
            'user' => $user
        ], 200);
    }

    public function searchSpecialistName(Request $request){

        $data = User::whereNotNull('specializationID')
                ->where('firstName','LIKE','%'.$request->name.'%')
                ->orWhere('lastName','LIKE','%'.$request->name.'%')
                ->get();


        if(count($data)>0){
            return response()->json([
                'doctors' => $data,
                'status' => 200
            ], 200);
        }

        return response()->json([
            'message' => 'No Specialist found. Try to search again!',
            'status' => 200,
        ], 200);

    }

    public function searchSpecialSpeciality(Request $request){
        $data = Specialization::where('specializationName','LIKE','%'.$request->specializationName.'%')
                ->pluck('specializationID');

        if(count($data)>0){

            $doctors  = array();

            foreach($data as $d){
                $x = User::where('specializationID', $d)->get();

                array_push($doctors, $x);
            }

            return response()->json([
                'doctors' => $doctors,
                'status' => 200
            ], 200);
        }

        return response()->json([
            'message' => 'No Specialist found. Try to search again!',
            'status' => 200,
        ], 200);
    }


    public function getUserDetails(Request $request){
        $checkIfUserExists = User::where('userID', $request->id)->exists();

        if($checkIfUserExists){
            $user = User::where('userID', $request->id)->first();

            return response()->json([
                'user' => $user,
                'status' => 200,
            ], 200);
        }

        return response()->json([
            'message' => 'User doesn\'t exist',
            'status' => 400,
        ], 400);
    }

    public function updateUserProfile(Request $request){

        $checkIfClientExists = User::where('userID', $request->userID)->exists();

        //check if client ID does exist
        if($checkIfClientExists){
            
            //check if image is updated
            if($request->hasFile('profile')){
                $filename = time().'.'.$request->profile->getClientOriginalExtension();
                $request->profile->move(public_path('images/profilepic'), $filename);

                User::where('userID',  $request->userID)->update([
                    'dateOfBirth' => $request->age,
                    'gender' => $request->gender,
                    'regionID' => $request->regionID,
                    'user_image' => "http://lyfplus.co.tz/app/public/images/profilepic/$filename",
                    'street' => $request->street,
                    'height' => $request->height,
                    'weight' => $request->weight,
                    'blood_group' => $request->blood_group,
                    'allergy' => $request->allergy
                ]);

                return response()->json([
                    'user' => User::where('userID', $request->userID)->first(),
                    'message' => 'User updated successfully',
                    'status' => 200
                ], 200);
            }

           User::where('userID',  $request->userID)->update([
                'dateOfBirth' => $request->birthdate,
                'gender' => $request->gender,
                'regionID' => $request->regionID,
                'street' => $request->address,
                'height' => $request->height,
                'weight' => $request->weight,
                'blood_group' => $request->blood_group,
                'allergy' => $request->allergy
            ]);

            return response()->json([
                'user' => User::where('userID', $request->userID)->first(),
                'message' => 'User updated successfully',
                'status' => 200
            ], 200);

        }

        return response()->json([
            'message' => 'This client ID does not exist',
            'status' => 400
        ], 400);
    }


    public function updateSpecialistProfile(Request $request){
        //userID
         //gender
        //birthdate
        //countryID
        //regionID
        //address
        //image
    }

}