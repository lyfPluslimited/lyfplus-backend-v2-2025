<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Chat;

class ChatController extends Controller
{
    public function saveImage(Request $request){
        
        if($request->hasFile('image')){
            $file = $request->file('image');
            
            if(!$file->isValid()) {
                return response()->json(['message' => 'Invalid file upload'], 422);
            }
            
             //save image to specific path
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/chat'), $filename);
            
            $imgUrl = "http://167.172.12.18/app/public/images/chat/".$filename;
    
            return response()->json([
                'imageUrl' =>  $imgUrl,
                'status' => 200, 
            ], 200);
        } else {
            return response()->json([
                'message' => 'No image sent',
                'status' => 422, 
            ], 422);
        }
    }

    public function storeChat(Request $request){
        $validator = Validator::make($request->all(), [
            'patientID' => 'required',
            'specialistID' => 'required'
        ]);

        if ($validator->fails()) {
            return \response()->json('Please enter all details', 400);
        }

        Chat::updateOrCreate(
            ['patient_id' =>  $request->patientID,
            'specialist_id' => $request->specialistID],
            ['initiation_time' => $request->initiationTime]
        );

        return response()->json('Chat saved', 200);
    }

    public function updateSessionTime(Request $request){
        $chat = Chat::where([
            'patient_id' =>  $request->patientID,
            'specialist_id' => $request->specialistID
        ])->first();

        $chat->update([
            'initiation_time' => $request->initiationTime
        ]);

        return response()->json('Initiation time updated',200);
    }

    public function getDoctorChatHistory(Request $request, $id){
        $history = Chat::where('specialist_id', $id)->with(['doctor.specialization', 'client'])->orderBy('initiation_time', 'desc')->get();
        return response()->json($history, 200);
    }

    public function getPatientChatHistory(Request $request,$id){
        $history = Chat::where('patient_id', $id)->with(['doctor', 'client'])->orderBy('initiation_time', 'desc')->get();
        return response()->json($history, 200);
    }

    public function endSession(Request $request){
        $chat = Chat::where([
            'patient_id' =>  $request->patientID,
            'specialist_id' => $request->specialistID
        ])->first();

        $chat->update([
            'is_session_active' => false
        ]);

        return response()->json('Session no longer active', 200);
    }
}
