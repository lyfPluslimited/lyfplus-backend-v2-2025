<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\HomeService;
use App\Models\DoctorService;
use App\Models\ServiceConfirmation;
use App\Models\User;

class HomeServicesController extends Controller
{
    //get all services
    public function getServices(){
        $services = HomeService::get();

        return response()->json($services, 200);
    }

    //get doctors from specific service
    public function doctorsFromService($id){
        return response()->json(HomeService::where('home_services_id',$id)->with('doctors')->first(), 200); 
    }

    //get services related to doctor
    public function getDoctorServices($id){
        $user = User::where('userID', $id)->with('services')->first();
        return response()->json($user->services, 200);
    }
    
    //add a service
    public function addService(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => 'An input value is missing' ], 400);
        }

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/homeServices'), $filename);

            $service = new HomeService;
            $service->service_name = $request->name;
            $service->service_image = "http://167.172.12.18/app/public/images/homeServices/$filename";
            $service->timeStamp = date("Y-m-d H:i:s");
            $service->save();

            return response()->json('Service saved', 201);
        }

        return response()->json('Failed to save service', 401);
    }

        //add doctor to service via mobile app
        public function addDoctorToServiceApp(Request $request){
            date_default_timezone_set('Africa/Dar_es_Salaam');
    
            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required',
                'service_id' => 'required',
            ]);
    
            $user = User::where([
                'userID' => $request->doctor_id
            ])->first();
    
            if($user->doctorsIDverificationStatus == 'Not Verified'){
                return response()->json('Your not registered, you can\'t join home service', 400);
            }
    
            DoctorService::firstOrCreate([
                'doctor_id' => $user->userID,
                'service_id' => $request->service_id
            ],['timestamp' => date("Y-m-d H:i:s")]);

            $homeservicename = HomeService::where('home_services_id',$request->service_id)->pluck('service_name')->first();

            //send message to William 2
            (new \App\Models\FastHub)->sendSMS(255745247261, 'Dr. '.$user->lastName.' with phone number '.$user->phone.' has requested to provide home services on '.$homeservicename);

            //send message to William 1
            (new \App\Models\FastHub)->sendSMS(255713783398, 'Dr. '.$user->lastName.' with phone number '.$user->phone.' has requested to provide home services on '.$homeservicename);

            //send message to Kevin
            (new \App\Models\FastHub)->sendSMS(255782835136, 'Dr. '.$user->lastName.' with phone number '.$user->phone.' has requested to provide home services on '.$homeservicename);
    
            return response()->json('Your request has been submitted', 201);
        } 

    //add doctor to service via admin dashboard
    public function addDoctorToService(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'service_id' => 'required',
        ]);

        $user = User::where([
            'userID' => $request->doctor_id
        ])->first();

        if($user->doctorsIDverificationStatus == 'Not Verified'){
            return response()->json('Doctor not registered; can\'t join home service', 400);
        }

        DoctorService::firstOrCreate([
            'doctor_id' => $user->userID,
            'service_id' => $request->service_id
        ],['timestamp' => date("Y-m-d H:i:s")]);

        return response()->json('Your request has been submitted', 201);
    }  
    
    //approve or disapprove doctor
    public function doctorApproval($homeserviceid,$doctorid){
        $service = DoctorService::where([
            'doctor_id' => $doctorid,
            'service_id' => $homeserviceid
        ])->first();

        $service->update([
            'approved' => !$service->approved
        ]);

        if ($service->approved == true) {
            $doctor = User::where('userID', $doctorid)->first();
            $homeservicename = HomeService::where('home_services_id',$homeserviceid)->pluck('service_name')->first();

            $phonenumber = ltrim($doctor->phone, '+');

            (new \App\Models\FastHub)->sendSMS($phonenumber, 'You have been approved to work on '.$homeservicename.' service.');
            
        }

        return response()->json('Doctor status updated',200);
    }

    //update service from admin dashboard
    public function updateService(Request $request, $id){
        $service = HomeService::where('home_services_id', $id)->first();

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/homeServices'), $filename);

            $service->service_name = $request->name;
            $service->service_image = "http://167.172.12.18/app/public/images/homeServices/$filename";
            
            if($service->update([
                'service_name' => $request->name,
                'service_image' => "http://167.172.12.18/app/public/images/homeServices/$filename"
            ])){
                return response()->json('Service updated', 200);
            };

            return response()->json('Failed to update service', 400);
        }

        if($service->update([
            'service_name' => $request->name
        ])){
            return response()->json('Service updated', 200);
        };

        return response()->json('Failed to update service', 400);
    }


    //delete doctor from service
    public function removeDoctorFromService($id){
        $doctor = DoctorService::where('doctor_id', $id)->first();

        if($doctor->delete()){
            return response()->json('Doctor deleted', 200);
        }

        return response()->json('Failed to delete doctor', 400);
    }

    //delete service
    public function deleteService($id){
        $service = HomeService::where('home_services_id', $id)->first();

        $doctors = DoctorService::where('service_id', $id)->get();

        foreach($doctors as $doctor){
            $doctor->delete();
        }

        if($service->delete()){
            return response()->json('Service deleted', 200);
        }
        return response()->json('Failed to delete service', 400);
    }

    //confirm service
    public function confirmService(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $confirm = new ServiceConfirmation;
        $confirm->user_id = $request->userID;
        $confirm->service_id = $request->service_id;
        $confirm->phone = $request->phonenumber;
        $confirm->visitation_date = $request->date;
        
        if($confirm->save()){

            $client = User::where('userID', $confirm->user_id)->first();
            $service = HomeService::where('home_services_id', $confirm->service_id)->first();

            $message = 'Patient '.$client->firstName. ' '. $client->lastName. ' with phone number '. $confirm->phone .' is requesting a home service '. $service->service_name .' on '. $confirm->visitation_date.' on '.date('d/m/Y H:i:s').'. Please attend to him fast.';

            //send message to William 2
            (new \App\Models\FastHub)->sendSMS(255745247261, $message);

            //send message to William 1
            (new \App\Models\FastHub)->sendSMS(255713783398, $message);

            //send message to Kevin
            (new \App\Models\FastHub)->sendSMS(255782835136, $message);

            return response()->json('Service confirmed by client', 201);
        }
        return response()->json('Service failed to be confirmed by client', 401);
    }

    public function confirmedRequests(){
        $visits = ServiceConfirmation::with(['client', 'service'])->get();

        return response()->json($visits, 200);
    }

}
