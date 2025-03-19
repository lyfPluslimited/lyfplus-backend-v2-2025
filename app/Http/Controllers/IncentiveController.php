<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kpi;
use App\Models\OnlineTime;
use App\Models\KpiPrice;
use App\Models\KpiTracking;
use App\Models\Incentive;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncentiveController extends Controller
{    

    public function postSharing($doctorID){
        //get doctor details
        $doctor = User::where('userID', $doctorID)->first();

        //check if doctor is in the incentive scheme
        if(!$doctor->incentive_doctor){
            return;
        }

         //check if any records of doctor exists today
         $checkiftracking = KpiTracking::where('doctor_id', $doctorID)
         ->whereDate('created_at', Carbon::today())->exists();

         if($checkiftracking){
            $tracking = KpiTracking::where('doctor_id', $doctorID)
            ->whereDate('created_at', Carbon::today())->first();
        } else{
            $tracking = KpiTracking::create([
            'doctor_id' => $doctorID,
            'amount' => 0,
            'paid' => false
            ]);
        }

        //get amount of times task is done today
        $incentiveCollection = Incentive::where([
            'kpi_id' => 4,
            'doctor_id' => $doctorID,
            'tracking_id' => $tracking->id,
        ])->whereDate('created_at', Carbon::today())->get();

        //get KPI default quantity
        $kpiQuantity = Kpi::where('id', 4)->pluck('default_unit_quantity')->first();

        //get KPI price 
        $kpiAmount = KpiPrice::where(['kpi_id' => 4, 'doctor_id' => $doctorID ])->pluck('unit_amount')->first();

        //add amount if KPI quantity is not exceeded
        if(count($incentiveCollection) < $kpiQuantity ){
            $tracking->update([
                'paid' => false,
                'amount' => $tracking->amount + $kpiAmount
            ]);
        }
        
        //add kpi to incentive list
        Incentive::create([
            'kpi_id' => 4, 
            'doctor_id' => $doctorID, 
            'tracking_id' => $tracking->id
        ]);

        return;  
    }

    /**
     * patientSignupKpi
     *
     * @param  integer $doctorID
     * @return void
     */
    public function patientSignupKpi($doctorID){
        //get doctor details
        $doctor = User::where('userID', $doctorID)->first();

        //check if doctor is in the incentive scheme
        if(!$doctor->incentive_doctor){
            return;
        }

         //check if any records of doctor exists today
         $checkiftracking = KpiTracking::where('doctor_id', $doctorID)
         ->whereDate('created_at', Carbon::today())->exists();

        if($checkiftracking){
            $tracking = KpiTracking::where('doctor_id', $doctorID)
            ->whereDate('created_at', Carbon::today())->first();
        } else{
            $tracking = KpiTracking::create([
            'doctor_id' => $doctorID,
            'amount' => 0,
            'paid' => false
            ]);
        }

        //get amount of times task is done today
        $incentiveCollection = Incentive::where([
            'kpi_id' => 2,
            'doctor_id' => $doctorID,
            'tracking_id' => $tracking->id,
            ])->whereDate('created_at', Carbon::today())->get();

        //get KPI default quantity
        $kpiQuantity = Kpi::where('id', 2)->pluck('default_unit_quantity')->first();

        //get KPI price 
        $kpiAmount = KpiPrice::where(['kpi_id' => 2, 'doctor_id' => $doctorID ])->pluck('unit_amount')->first();

        //add amount if KPI quantity is not exceeded
        if(count($incentiveCollection) < $kpiQuantity ){
            $tracking->update([
                'paid' => false,
                'amount' => $tracking->amount + $kpiAmount
            ]);
        }
        
        //add kpi to incentive list
        Incentive::create([
            'kpi_id' => 2, 
            'doctor_id' => $doctorID, 
            'tracking_id' => $tracking->id
        ]);

        return;    
    }

    
    /**
     * patientInvitationKpi
     *
     * @param  integer $doctorID
     * @return void
     */
    public function patientInvitationKpi($doctorID){
        //get doctor details
        $doctor = User::where('userID', $doctorID)->first();

        //check if doctor is in the incentive scheme
        if(!$doctor->incentive_doctor){
            return;
        }

        //check if any records of doctor exists today
        $checkiftracking = KpiTracking::where('doctor_id', $doctorID)
                        ->whereDate('created_at', Carbon::today())->exists();

        if($checkiftracking){
            $tracking = KpiTracking::where('doctor_id', $doctorID)
            ->whereDate('created_at', Carbon::today())->first();
        } else{
            $tracking = KpiTracking::create([
                'doctor_id' => $doctorID,
                'amount' => 0,
                'paid' => false
            ]);
        }

        //get amount of times task is done today
        $incentiveCollection = Incentive::where([
            'kpi_id' => 1,
            'doctor_id' => $doctorID,
            'tracking_id' => $tracking->id,
            ])->whereDate('created_at', Carbon::today())->get();

        //get KPI default quantity
        $kpiQuantity = Kpi::where('id', 1)->pluck('default_unit_quantity')->first();

        //get KPI price 
        $kpiAmount = KpiPrice::where(['kpi_id' => 1, 'doctor_id' => $doctorID ])->pluck('unit_amount')->first();

        //add amount if KPI quantity is not exceeded
        if(count($incentiveCollection) < $kpiQuantity ){
            $tracking->update([
                'paid' => false,
                'amount' => $tracking->amount + $kpiAmount
            ]);
        }

        //add kpi to incentive list
        Incentive::create([
            'kpi_id' => 1, 
            'doctor_id' => $doctorID, 
            'tracking_id' => $tracking->id
        ]);

        return;        
    }

    /**
     * displayKpis
     *
     * @return array
     */
    public function displayKpis(){
        return response()->json(Kpi::get(), 200);
    }

    public function updateKpiQuantity(Request $request){
        Kpi::where('id', 1)->update([
            'default_unit_quantity' => $request->invitationqnty
        ]);

        Kpi::where('id', 2)->update([
            'default_unit_quantity' => $request->signupqnty
        ]);

        Kpi::where('id', 3)->update([
            'default_unit_quantity' => $request->onlineTimeqnty
        ]);

        Kpi::where('id', 4)->update([
            'default_unit_quantity' => $request->forumqnty
        ]);

        Kpi::where('id', 5)->update([
            'default_unit_quantity' => $request->qnqnty
        ]);

        return response()->json('Kpi quantity updated', 200);
    }
    
    /**
     * displayDoctors
     *
     * @return array
     */
    public function displayDoctors(){
        $doctors = User::where('incentive_doctor', true)->with('tracking')->get();
        return \response()->json($doctors, 200);
    }
        
    /**
     * storeKpi
     *
     * @param  string $name
     * @param integer $amount
     * @param integer $quantity
     * @return void
     */
    public function storeKpi(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required', 
            'quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json('Please enter all details', 301);
        }

        //store kpi
        Kpi::create([
            'kpi_name' => $request->name,
            'default_unit_amount' => $request->amount, 
            'default_unit_quantity' => $request->quantity,
        ]);

        return response()->json('KPI stored', 201);
    }
    
    /**
     * deleteKpi
     *
     * @param  integer $id
     * @return string
     */
    public function deleteKpi($id){
        $kpi = Kpi::find($id);
        $kpi->delete();
        return response()->json('KPI deleted', 200);
    }
    
    /**
     * changeIncentiveStatus
     *
     * @param  integer $id
     * @return string
     */
    public function changeIncentiveStatus($id){
        $doctor =  User::where('userID', $id)->first();
 
        $doctor->update([
            'incentive_doctor' => !$doctor->incentive_doctor
        ]);

        $checkIfDoctorHasKPIPrices = KpiPrice::where('doctor_id', $id)->exists();

        if(!$checkIfDoctorHasKPIPrices){
            KpiPrice::create([
                'kpi_id' => 1, 
                'doctor_id' => $id, 
                'unit_amount' => 2000
             ]);
    
             KpiPrice::create([
                'kpi_id' => 2, 
                'doctor_id' => $id, 
                'unit_amount' => 5000
             ]);
    
             KpiPrice::create([
                'kpi_id' => 3, 
                'doctor_id' => $id, 
                'unit_amount' => 5000
             ]);
    
             KpiPrice::create([
                'kpi_id' => 4, 
                'doctor_id' => $id, 
                'unit_amount' => 3000
             ]);
    
             KpiPrice::create([
                'kpi_id' => 5, 
                'doctor_id' => $id, 
                'unit_amount' => 3000
             ]);
        }
 
        return response()->json('Incentive status changed', 200);
     }

     public function getKpiPriceforDoctor($id){
         return response()->json(KpiPrice::where('doctor_id', $id)->get(), 200);
     }

     public function updateKPIDoctorPrices(Request $request,$id){
         KpiPrice::where(['kpi_id' => 1, 'doctor_id' => $id])->update([
            'unit_amount' => $request->invitationPrice
         ]);

         KpiPrice::where(['kpi_id' => 2, 'doctor_id' => $id])->update([
            'unit_amount' => $request->successfulSignUpPrice
         ]);

         KpiPrice::where(['kpi_id' => 3, 'doctor_id' => $id])->update([
            'unit_amount' => $request->onlineTimePrice
         ]);

         KpiPrice::where(['kpi_id' => 4, 'doctor_id' => $id])->update([
            'unit_amount' => $request->forumPostPrice
         ]);

         KpiPrice::where(['kpi_id' => 5, 'doctor_id' => $id])->update([
            'unit_amount' => $request->qnAnsPrice
         ]);

         User::where('userID', $id)->update([
             'incentive_percentage' => (int)$request->consultationPercentage/100
         ]);

         return response()->json('Kpi prices updated', 200);
     }

     public function setKpiPrices(Request $request, $id){
         KpiPrice::firstOrCreate([
            'kpi_id' => 1, 
            'doctor_id' => $id, 
            'unit_amount' => $request->invitationPrice
         ]);

         KpiPrice::firstOrCreate([
            'kpi_id' => 2, 
            'doctor_id' => $id, 
            'unit_amount' => $request->successfulSignUpPrice
         ]);

         KpiPrice::firstOrCreate([
            'kpi_id' => 3, 
            'doctor_id' => $id, 
            'unit_amount' => $request->onlineTimePrice
         ]);

         KpiPrice::firstOrCreate([
            'kpi_id' => 4, 
            'doctor_id' => $id, 
            'unit_amount' => $request->forumPostPrice
         ]);

         KpiPrice::firstOrCreate([
            'kpi_id' => 5, 
            'doctor_id' => $id, 
            'unit_amount' => $request->qnAnsPrice
         ]);

         return response()->json('Kpi prices set', 200);
     }

     public function onlineStatus(Request $request){
        
        $validator = Validator::make($request->all(), [
            'doctorID' => 'required',
            'status' => 'required', //1 for online, 2 for offline
            'time' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json('Please fill in all inputs', 422);
        }

        $doctorID = $request->doctorID;

        //get doctor details
        $doctor = User::where('userID', $doctorID)->first();

        //check if doctor is in the incentive scheme
        if(!$doctor->incentive_doctor){
            return;
        }

        if($request->status == '1'){
            OnlineTime::create([
                'doctor_id' => $doctorID,
                'online_time' => $request->time,
            ]);
        } else{
            $data = OnlineTime::where('doctor_id', $doctorID)
                                ->latest('id')->first();

            $data->update([
                'doctor_id' => $doctorID,
                'offline_time' => $request->time,
            ]);
        }

        if($request->status == '2'){

            //get online times for the day
        $times = OnlineTime::where('doctor_id',$doctorID)
        ->whereDate('created_at', Carbon::today())->get();

            $calculatedTime = 0;

            //calculate amount of time doctor is online
            foreach ($times as $value) {
                $calculatedTime += ($value->offline_time - $value->online_time);
            }

            //check if any records of doctor exists today
            $checkiftracking = KpiTracking::where('doctor_id', $doctorID)
            ->whereDate('created_at', Carbon::today())->exists();

            if($checkiftracking){
                $tracking = KpiTracking::where('doctor_id', $doctorID)
                ->whereDate('created_at', Carbon::today())->first();
            } else{
            $tracking = KpiTracking::create([
                'doctor_id' => $doctorID,
                'amount' => 0,
                'paid' => false
                ]);
            }

            //get amount of times task is done today
            $incentiveCollection = Incentive::where([
            'kpi_id' => 3,
            'doctor_id' => $doctorID,
            'tracking_id' => $tracking->id,
            ])->whereDate('created_at', Carbon::today())->get();

            //get KPI default quantity
            $kpiQuantity = Kpi::where('id', 3)->pluck('default_unit_quantity')->first();

            //get KPI price 
            $kpiAmount = KpiPrice::where(['kpi_id' => 3, 'doctor_id' => $doctorID ])->pluck('unit_amount')->first();

            //if calculated time is greater than 1 hour, add to tracking and add KPI

            switch ($calculatedTime) {
            case (3600000 < $calculatedTime && $calculatedTime < (3600000*2)) :
                    if(count($incentiveCollection) < 1){

                        $tracking->update([
                            'paid' => false,
                            'amount' => $tracking->amount + $kpiAmount
                        ]);
                        
                        //add kpi to incentive list
                        Incentive::create([
                            'kpi_id' => 3, 
                            'doctor_id' => $doctorID, 
                            'tracking_id' => $tracking->id
                        ]);

                    }
                break;

            case ((3600000*2) < $calculatedTime && $calculatedTime < (3600000*3)):
                if(count($incentiveCollection) < 2){

                    $tracking->update([
                        'paid' => false,
                        'amount' => $tracking->amount + $kpiAmount
                    ]);
                    
                    //add kpi to incentive list
                    Incentive::create([
                        'kpi_id' => 3, 
                        'doctor_id' => $doctorID, 
                        'tracking_id' => $tracking->id
                    ]);

                }
                break;

            case ($calculatedTime < (3600000*3)):
                if(count($incentiveCollection) < 3){

                    $tracking->update([
                        'paid' => false,
                        'amount' => $tracking->amount + $kpiAmount
                    ]);
                    
                    //add kpi to incentive list
                    Incentive::create([
                        'kpi_id' => 3, 
                        'doctor_id' => $doctorID, 
                        'tracking_id' => $tracking->id
                    ]);

                }
                break;

            default:
                return;
                break;
            }

        }   

        return response()->json('Time saved', 200);
     }
     
     /**
      * getDoctorUnpaidAmout
      *
      * @param  mixed $id
      * @return int $amount
      */
     public function getDoctorUnpaidAmount($id){
        $trackings = KpiTracking::where(['doctor_id' => $id, 'paid' => false])->get();

        $amount = 0;

        foreach ($trackings as $value) {
            $amount += $value->amount;
        }

        return response()->json($amount, 200);
     }

     public function getDoctorKpis($doctorId){
        $unpaidTrackings = KpiTracking::where(
            [
                'doctor_id' => $doctorId,
                'paid' => false
             ])->with('incentivesDone.kpi')->get();

        return \response()->json($unpaidTrackings,200);
     }

     public function doctorPrices($doctorId){
        $doctorPrices = User::where('userID', $doctorId)->with('prices')->first();
        return \response()->json($doctorPrices,200);
     }

     public function getGeneralKpis($doctorId){
        $unpaidTrackings = KpiTracking::where('doctor_id',$doctorId)
                            ->with('incentivesDone.kpi')->get();

        return \response()->json($unpaidTrackings,200);
     }
     
     /**
      * getKpisInRange
      *
      * @param  mixed $request
      * @param  mixed $doctorId
      * @return array
      */
     public function getKpisInRange(Request $request, $doctorId){

        $start = Carbon::createFromFormat('d/m/Y', $request->start)->toDateTimeString();
        $end = Carbon::createFromFormat('d/m/Y', $request->end)->toDateTimeString();

        $unpaidTrackings = KpiTracking::where('doctor_id',$doctorId)
        ->whereBetween('created_at', [$start,$end])->with('incentivesDone.kpi')->get(); 

        return \response()->json($unpaidTrackings,200);
     }

     public function getKpisInRangeMobile(Request $request, $doctorId){

        $start = Carbon::createFromFormat('d/m/Y', $request->start)->toDateTimeString();
        $end = Carbon::createFromFormat('d/m/Y', $request->end)->toDateTimeString();

        $unpaidTrackings = KpiTracking::where('doctor_id',$doctorId)
        ->whereBetween('created_at', [$start,$end])->with('incentivesDone.kpi')->get(); 

        $collection = $unpaidTrackings->map(function($data, $key) {
            $data->date_created = strtotime($data->created_at)*1000;
            return $data;
          });

        return \response()->json($collection->all(),200);
     }

     public function getGeneralKpisMobile($doctorId){
         
        $unpaidTrackings = KpiTracking::where('doctor_id', $doctorId)->with('incentivesDone.kpi')
                    ->get();
        
        $collection = $unpaidTrackings->map(function($data, $key) {
            $data->date_created = strtotime($data->created_at)*1000;
            return $data;
          });

        return \response()->json($collection->all(),200);
     }
     
     /**
      * makeIncentivePayment
      *
      * @param  mixed $doctorId
      * @return void
      */
     public function makeIncentivePayment($doctorId){
        KpiTracking::where([
            'doctor_id' => $doctorId,
            'paid' => false
        ])->update([
            'paid' => true
        ]);

        return \response()->json('Doctor paid', 200);
     }
 
}
