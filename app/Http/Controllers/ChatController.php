<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Chat;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="LyfPlus Chat API Documentation",
 *      description="API documentation for managing chat sessions",
 *      @OA\Contact(
 *          email="kmisigaro@outlook.com"
 *      ),
 * )
 */
class ChatController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/send-image",
     *      operationId="saveImage",
     *      tags={"Chat"},
     *      summary="Upload an image for chat",
     *      description="Uploads an image and returns its URL.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="image", type="string", format="binary")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Image uploaded successfully.",
     *          @OA\JsonContent(
     *              @OA\Property(property="imageUrl", type="string"),
     *              @OA\Property(property="status", type="integer")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Invalid file upload or no image sent.")
     * )
     */
    public function saveImage(Request $request){
        if($request->hasFile('image')){
            $file = $request->file('image');
            if(!$file->isValid()) {
                return response()->json(['message' => 'Invalid file upload'], 422);
            }
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/chat'), $filename);
            $imgUrl = "http://167.172.12.18/app/public/images/chat/".$filename;
            return response()->json(['imageUrl' =>  $imgUrl, 'status' => 200], 200);
        }
        return response()->json(['message' => 'No image sent', 'status' => 422], 422);
    }

    /**
     * @OA\Post(
     *      path="/api/chat/store",
     *      operationId="storeChat",
     *      tags={"Chat"},
     *      summary="Store a chat session",
     *      description="Creates or updates a chat session.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"patientID", "specialistID"},
     *              @OA\Property(property="patientID", type="integer"),
     *              @OA\Property(property="specialistID", type="integer"),
     *              @OA\Property(property="initiationTime", type="string", format="date-time")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Chat saved successfully."),
     *      @OA\Response(response=400, description="Missing required details.")
     * )
     */
    public function storeChat(Request $request){
        $validator = Validator::make($request->all(), [
            'patientID' => 'required',
            'specialistID' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json('Please enter all details', 400);
        }
        Chat::updateOrCreate(
            ['patient_id' =>  $request->patientID, 'specialist_id' => $request->specialistID],
            ['initiation_time' => $request->initiationTime]
        );
        return response()->json('Chat saved', 200);
    }

    /**
     * @OA\Put(
     *      path="/api/chat/updateSessionTime",
     *      operationId="updateSessionTime",
     *      tags={"Chat"},
     *      summary="Update chat session initiation time",
     *      description="Updates the initiation time of an existing chat session.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"patientID", "specialistID", "initiationTime"},
     *              @OA\Property(property="patientID", type="integer"),
     *              @OA\Property(property="specialistID", type="integer"),
     *              @OA\Property(property="initiationTime", type="string", format="date-time")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Initiation time updated successfully.")
     * )
     */
    public function updateSessionTime(Request $request){
        $chat = Chat::where(['patient_id' => $request->patientID, 'specialist_id' => $request->specialistID])->first();
        $chat->update(['initiation_time' => $request->initiationTime]);
        return response()->json('Initiation time updated',200);
    }

    /**
     * @OA\Get(
     *      path="/api/chat/getDoctorChatHistory/{id}",
     *      operationId="getDoctorChatHistory",
     *      tags={"Chat"},
     *      summary="Retrieve doctor's chat history",
     *      description="Fetches all chat sessions for a specific doctor.",
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Chat history retrieved successfully.")
     * )
     */
    public function getDoctorChatHistory(Request $request, $id){
        $history = Chat::where('specialist_id', $id)->with(['doctor.specialization', 'client'])->orderBy('initiation_time', 'desc')->get();
        return response()->json($history, 200);
    }

    /**
     * @OA\Post(
     *      path="/api/chat/endSession",
     *      operationId="endSession",
     *      tags={"Chat"},
     *      summary="End a chat session",
     *      description="Marks a chat session as inactive.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"patientID", "specialistID"},
     *              @OA\Property(property="patientID", type="integer"),
     *              @OA\Property(property="specialistID", type="integer")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Session ended successfully.")
     * )
     */
    public function endSession(Request $request){
        $chat = Chat::where(['patient_id' => $request->patientID, 'specialist_id' => $request->specialistID])->first();
        $chat->update(['is_session_active' => false]);
        return response()->json('Session no longer active', 200);
    }
}