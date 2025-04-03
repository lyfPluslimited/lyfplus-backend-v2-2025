<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialization;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Topics;
use App\Models\Symptom;
use App\Models\Abuse;
use App\Models\Forum;
use Kreait\Firebase\Factory;
use App\Models\SelcomModel;
use App\Models\DoctorSessions;
use App\Models\Article;
use App\Models\ConsultationHistory;
use App\Models\Comment;
use App\Models\FastHub;
use App\Models\ConsultationPeriod;
use App\Models\ConsultationPeriodHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function getSpecializations(){
        return response()->json(Specialization::all(), 200);
    }

    public function getHospitals(){
        return response()->json(Hospital::all(), 200);
    }

    public function getConsultationPeriod(){
        return response()->json(ConsultationPeriod::first(), 200);
    }

    public function updateConsultationPeriod(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $consultation = ConsultationPeriod::first();

        if($consultation->update([
            'period' => $request->period,
            'period_definition' => $request->period_definition,
            'date_set' => date("Y-m-d H:i:s")
        ])){
            $history = new ConsultationPeriodHistory;
            $history->period = $request->period;
            $history->period_def = $request->period_definition;
            $history->date_set = $consultation->date_set;
            $history->save();

            return response()->json('Consultation Period set', 200);
        }

        return response()->json('Failed to create consultation period', 400);
    }

    public function getPosts(){
        return response()->json(Forum::with(['author', 'postcategory'])->get(), 200);
    }

    public function getSinglePost($id){
        $post = Forum::where('userPostID', $id)->with(['author', 'postcategory'])->first();

        return response()->json($post, 200);
    }

    public function getFastHubMessages(){
        return response()->json(FastHub::all(), 200);
    }

    public function getArticles(){
        return response()->json(Article::with(['author', 'topic'])->get(), 200);
    }

    public function getSingleArticle($id){
        $article = Article::where('subID', $id)->with(['author', 'topic'])->first();

        return response()->json($article, 200);
    }

    public function getSingleTopic($id){
        $topic = Topics::where('topicID', $id)->first();

        return response()->json($topic, 200);
    }

    public function deleteTopic($id){
        $topic =  Topics::where('topicID', $id)->first();

        if($topic->delete()){
            return response()->json('Topic was deleted', 200);
        }

        return response()->json('Topic failed to be deleted', 400);
    }

    public function getUserComments(){
        $comments = Comment::with(['user', 'post'])->get();

        return response()->json($comments, 200);
    }

    public function getTopicss(){
        return response()->json(Topics::with('user')->get(), 200);
    }


    public function loginAdmin(Request $request){
        $checkIfAdminExists = User::where([
            ['email','=', $request->email],
            ['userRole', '=', 3]
            ])->exists();

        if($checkIfAdminExists){
            $admin =  User::where([
                ['email','=', $request->email],
                ['userRole', '=', 3]
                ])->first();

            if (Hash::check($request->password , $admin->password)) {
                // The passwords match...
                return response()->json([
                    'user' => $admin,
                    'auth_token' => time(),
                    'success' => true,
                    'message' => 'User authenticated'
                ], 200);
            }
            //if password dont match
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User doesn\'t exist'
        ], 200);
    }

    //article functions
    public function saveArticle(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/articles'), $filename);

            $article = new Article;
            $article->subTopicTitle = $request->title;
            $article->userID = 0;
            $article->timeStamp = date("Y-m-d H:i:s");;
            $article->causes = $request->causes;
            $article->topicID = $request->topic;
            $article->image = "http://lyfplus.co.tz/app/public/images/articles/$filename";
            $article->symptoms = $request->symptoms;
            $article->preventiveCureTreatment = $request->preventions;
            $article->description = $request->description;
            
            if($article->save()){
                return response()->json('Post saved', 201);
            }

            return response()->json('Post failed to be saved', 400);
        }

        $article = new Article;
        $article->subTopicTitle = $request->title;
        $article->userID = 0;
        $article->timeStamp = date("Y-m-d H:i:s");
        $article->causes = $request->causes;
        $article->topicID = $request->topic;
        $article->symptoms = $request->symptoms;
        $article->preventiveCureTreatment = $request->preventions;
        $article->description = $request->description;
        
        if($article->save()){
            return response()->json('Post saved', 201);
        }

        return response()->json('Post failed to be saved', 400);
    }

    public function updateArticle(Request $request){
        $article = Article::where('subID', $request->id)->first();

        if($article->update([
            'description' => $request->description,
            'causes' => $request->causes,
            'symptoms' => $request->symptoms,
            'preventiveCureTreatment' => $request->preventions,
            'subTopicTitle' => $request->title
        ])){
            return response()->json('Article updated', 200);
        }

        return response()->json('Article failed to be updated', 400);
    }

    public function deleteArticle($id){
        $article = Article::where('subID', $id)->first();

        if($article->delete()){
            return response()->json('Article deleted', 200);
        }

        return response()->json('Article failed to be deleted', 400);
    }   

    public function savePost(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $post = new Forum;
        $post->userID = 211;
        $post->description = $request->description;
        $post->category = $request->category;
        $post->userPrivacy = $request->userPrivacy;
        $post->title = $request->title;
        $post->timeStamp = date("Y-m-d H:i:s");;
        
        if($post->save()){
            return response()->json('Post saved', 201);
        }

        return response()->json('Failed to save post', 400);
    }

    public function getUsers(){
        return response()->json(User::all(),200);
    }

    public function getTopics(){
        return response()->json(Topics::all(), 200);
    }

    public function getSymptoms(){
        return response()->json(Symptom::all(), 200);
    }

    public function getAbuseReports(){
        return response()->json(Abuse::with(['reporter', 'post'])->get(), 200);
    }

    public function getSingleUser(Request $request, $userID){
        return response()->json(User::where('userID', $userID)->first(), 200);
    }

    public function getSelcomPayments(){
        
        $res = SelcomModel::with('client', 'doctor')->get();

        return response()->json($res, 200);
    }

    public function saveDoctorFee(Request $request){
        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        $ref = $database->getReference('specialist/'.$request->userID);

        if($ref->update([
            'consultation_fee' => $request->consultation_fee,
            'call_fee' => $request->call_fee
        ])){
            User::where('userID', $request->userID)->update([
                'consultation_fee' => (int)$request->consultation_fee,
                'call_fee' => (int)$request->call_fee
            ]);

            return response()->json('Fee\'s updated', 200);
        }

        return response()->json('Fee\'s failed to be updated', 200);
    }
    

    public function updateDoctorDetails(Request $request){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        //firebase functions
        $ref = $database->getReference('specialist/'.$request->userID);

        $specialization = Specialization::where('specializationID', $request->specializationID)->first();

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/profilepic'), $filename);
        }
        
        if($ref->update([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'phone' => $request->phone,
            'email' => $request->email,
            'specialization' => $specialization->specializationName,
            'location' => Hospital::where('specializationAreaID', $request->specilizationAreaID)->pluck('areaOfSpecialization')->first(),
        ])){

            User::where('userID', $request->userID)->update([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'phone' => $request->phone,
                'email' => $request->email,
                'specializationID' => $request->specializationID,
                'specilizationAreaID' => $request->specilizationAreaID,
            ]);

            return response()->json([
                'message' => 'Doctor updated successfully',
                'test' => $request->userID
            ], 200);

        }

        return response()->json('Failed to updated Doctor',400);
    }

    //HOSPITAL FUNCTIONS
    public function saveHospital(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/hospitals'), $filename);

            $hospital = new Hospital;
            $hospital->areaOfSpecialization = $request->name;
            $hospital->timeStamp = date("Y-m-d H:i:s");
            $hospital->address = $request->address;
            $hospital->areaOfSpecialization_image = 'http://lyfplus.co.tz/app/public/images/hospitals/'.$filename; 

            if($hospital->save()){
                return response()->json('Hospital saved', 200);
            }

            return response()->json('Hospital failed to be saved', 400);
        }

        $hospital = new Hospital;
        $hospital->areaOfSpecialization = $request->name;
        $hospital->timeStamp = date("Y-m-d H:i:s");
        $hospital->address = $request->address;

        if($hospital->save()){
            return response()->json('Hospital saved', 200);
        }

        return response()->json('Hospital failed to be saved', 400);
    }

    public function changeHospitalStatus($id){
        $hospital = Hospital::where('specializationAreaID', $id)->first();

        if($hospital->verificationStatus == null){
            $hospital->update([
                'verificationStatus' => 'Approved'
            ]);
        } else {
            $hospital->update([
                'verificationStatus' => null
            ]);
        }

        return response()->json('Complete', 200);
    }

    public function getSingleHospital($id){
        $hospital = Hospital::where('specializationAreaID', $id)->first();

        return response()->json($hospital, 200);
    }

    public function deleteHospital($id){
        $hospital = Hospital::where('specializationAreaID', $id)->first();

        if($hospital->delete()){
            return response()->json('Hospital deleted', 200);
        }

        return response()->json('Hospital failed to be deleted', 400);
    }

    public function saveTopic(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            'topicTitle' => 'required',
            'topic_image' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => 'Failed to validate topic details' ], 400);
        }

        $filename = time().'.'.$request->topic_image->getClientOriginalExtension();
        $request->topic_image->move(public_path('images/topics'), $filename);

        $topic = new Topics;
        $topic->topicTitle = $request->topicTitle;
        $topic->topic_image = 'http://lyfplus.co.tz/app/public/images/topics/'.$filename;
        $topic->additionDate = date("Y-m-d H:i:s");
        $topic->userID = 0;
        if($topic->save()){
            return response()->json('Topic saved', 200);
        }

        return response()->json('Topic failed to be saved', 400);
    }

    public function getDoctorTransaction($id){

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        $checkIfUserExists = User::where('userID', $id)->exists();

        if(!$checkIfUserExists){
            return response()->json('User does not exist', 400);
        }

        $checkIfReferenceExists = $database->getReference("Consultation/$id")->getSnapshot()->exists();

        if(!$checkIfReferenceExists){
            return response()->json([], 200);
        }

        $callRef = $database->getReference("Consultation/$id/call");

        $consultationRef = $database->getReference("Consultation/$id/consultation");

        $callRefKeys = $callRef->getChildKeys();
        $consultationRefKeys = $consultationRef->getChildKeys();

        foreach($callRefKeys as $callKey){
            $checkIfCallExists = DoctorSessions::where('order_id', $callKey)->exists();

            if(!$checkIfCallExists){

                $data = $database->getReference("Consultation/$id/call/$callKey")->getValue();

                $session = new DoctorSessions;
                $session->order_id = (int)$callKey;
                $session->doctor_id = $id;
                $session->session_start = $data['sessionStart'];
                $session->session_end = $data['sessionEnd'];
                $session->session_expired = $data['sessionExpires'];
                $session->consultation_type = 'call';
                $session->time_added = date("Y-m-d H:i:s");
                $session->save();
                
            }
        }

        foreach($consultationRefKeys as $consultKey){
            $checkIfConsultationExists = DoctorSessions::where('order_id', $consultKey)->exists();

            if(!$checkIfConsultationExists){

                $data = $database->getReference("Consultation/$id/call/$consultKey")->getValue();

                $session = new DoctorSessions;
                $session->order_id = (int)$consultKey;
                $session->doctor_id = $id;
                $session->session_start = $data['sessionStart'];
                $session->session_end = $data['sessionEnd'];
                $session->session_expired = $data['sessionExpires'];
                $session->consultation_type = 'message';
                $session->time_added = date("Y-m-d H:i:s");
                $session->save();

            }
        }

        return response()->json(
            DoctorSessions::where('doctor_id', $id)->get(), 200
        );

    }

    public function deleteDoctor(Request $request, $userID){
        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        $ref = $database->getReference('specialist/'.$userID);

        $doctor = User::where('userID', $userID)->first();

        if($doctor->deleted){
            $ref->update([
                'disabled' => false
            ]);

            $doctor->update([
                'deleted' => false
            ]);

            return response()->json('Doctor successfully enabled', 200);
        }

        $ref->update([
            'disabled' => true
        ]);

        $doctor->update([
            'deleted' => true
        ]);

        return response()->json('Doctor successfully disabled', 200);
    }

    // public function deleteDoctor(Request $request, $userID){

    //     $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
    //     $database = $factory->createDatabase();

    //     //firebase functions
    //     $ref = $database->getReference('specialist/'.$userID);

    //     $refObj = $ref->getValue();

    //     if(User::where('userID', $userID)->delete()){

    //         //delete user record from firebase database
    //         $ref->remove();

    //         //delete user from firebase authentication
    //         $auth = $factory->createAuth();
    //         $auth->deleteUser($refObj['uuid']);

    //         return response()->json('Doctor successfully deleted', 200);
    //     }

    //     $ref = $database->getReference('specialist/'.$userID);

    //     if($ref->update([
    //         'disabled' => true
    //     ])){
    //         $doctor = User::where('userID', $userID)->first();

    //         $doctor->update([
    //             'deleted' => true
    //         ]);

    //         return response()->json('Doctor successfully disabled', 200);
    //     }

    //     return response()->json('Failed to disable doctor', 400);
    // }


    public function deleteClient(Request $request, $userID){

        $factory = (new Factory)->withServiceAccount(__DIR__.'/LyfplusFirebase.json');
        $database = $factory->createDatabase();

        // //firebase functions
        // $ref = $database->getReference('client/'.$userID);

        // $refObj = $ref->getValue();

        // if(User::where('userID', $userID)->update([
        //     'deleted' => true
        // ])){

        //     //delete user record from firebase database
        //     $ref->remove();

        //     //delete user from firebase authentication
        //     $auth = $factory->createAuth();
        //     $auth->deleteUser($refObj['uuid']);

        //     return response()->json('Client successfully deleted', 200);
        // }

        // return response()->json('Failed to delete client', 400);

        $ref = $database->getReference('client/'.$userID);

        $client = User::where('userID', $userID)->first();

        if($client->deleted){
            $ref->update([
                'disabled' => false
            ]);

            $client->update([
                'deleted' => false
            ]);

            return response()->json('Client successfully enabled', 200);
        }

        $ref->update([
            'disabled' => true
        ]);

        $client->update([
            'deleted' => true
        ]);

        return response()->json('Client successfully disabled', 200);
    }

    //symptom functions
    public function deleteSymptom($id){
        $symptom = Symptom::where('symptoms_id', $id)->first();

        if($symptom->delete()){
            return response()->json('Symptom deleted', 200);
        }

        return response()->json('Symptom could not be deleted', 400);
    }

    public function saveSymptom(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');
        
        $data = new Symptom;
        $data->symptom = $request->name;
        $data->date_added = date("Y-m-d H:i:s");

        if($data->save()){
            return response()->json('Symptom added', 200);
        }

        return response()->json('Symptom could not be saved', 400);
    }

    //specialization functions
    public function saveSpecialization(Request $request){
        date_default_timezone_set('Africa/Dar_es_Salaam');

        if($request->hasFile('image')){
            $filename = time().'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/specialization'), $filename);

            $data = new Specialization;
            $data->specializationName = $request->name;
            $data->additionDate = date("Y-m-d H:i:s");
            $data->specializationName_sw = $request->swahili;
            $data->specilizationIcon = 'http://lyfplus.co.tz/app/public/images/specialization/'.$filename;

            if($data->save()){
                return response()->json('Add new specialization', 200);
            }

            return response()->json('Specialization could not be added', 400);
        }

        $data = new Specialization;
        $data->specializationName = $request->name;
        $data->additionDate = date("Y-m-d H:i:s");
        $data->specializationName_sw = $request->swahili;

        if($data->save()){
            return response()->json('Add new specialization', 200);
        }

        return response()->json('Specialization could not be added', 400);

    }

    public function updateHospital(Request $request){
        $hospital = Hospital::where('specializationAreaID', $request->id)->first();

        if($hospital->update([
            'areaOfSpecialization' => $request->name,
            'address' => $request->address
        ])){
            return response()->json('Hospital Updated', 200);
        }

        return response()->json('Could not update Hospital', 400);
    }

    public function updateSpecialization(Request $request){
        $specialization = Specialization::where('specializationID', $request->id)->first();

        if($specialization->update([
            'specializationName' => $request->name,
            'specializationName_sw' => $request->swahili
        ])){
            return response()->json('Specialization Updated', 200);
        }

        return response()->json('Could not update specialziation', 400);
    }

    public function deleteSpecialization($id){
        $spec = Specialization::where('specializationID', $id)->first();
        if($spec->delete()){
            return response()->json('Specialization deleted', 200);
        }

        return response()->json('Specialization could not be deleted', 400);
    }

    public function getDoctors(){
        $doctors =  User::where('userRole', 2)->with(['consultationPayments','subscriptionPayments'])->get();
        return response()->json($doctors, 200);
    }

    public function consultationHistory(Request $request){

        date_default_timezone_set('Africa/Dar_es_Salaam');

        $validator = Validator::make($request->all(), [
            'doctorID' => 'required', 
            'patientID' => 'required', 
            'type' => 'required', 
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json('Please enter all values', 400);
        }

        $history = ConsultationHistory::create([
            'doctor_id' => $request->doctorID, 
            'patient_id' => $request->patientID, 
            'consultation_type' => $request->type, 
            'amount' => $request->amount,
        ]);

        $message = 'Patient '.$history->patient->firstName.' '.$history->patient->lastName.' has initiated '.$history->consultation_type.' consultation with Dr. '.$history->doctor->firstName.' '.$history->doctor->lastName.' for '.$history->amount.' TZS on '.$history->created_at->format('l jS \of F Y h:i A');

        //send message to William 1
        (new \App\Models\FastHub)->sendSMS(255713783398, $message);

        //send message to William 2
        (new \App\Models\FastHub)->sendSMS(255745247261, $message);

        //send message to Kevin
        (new \App\Models\FastHub)->sendSMS(255782835136, $message);

        return response()->json($message, 200);
    }

    public function doctorVerification($id){
       $doctor =  User::where('userID', $id)->update([
            'doctorsIDverificationStatus' => $doctor->doctorsIDverificationStatus == 'Verified' ? 'Not Verified' : 'Verified' ,
            'consultation_fee' => 1000,
            'call_fee' => 1000,
        ]);

        return response()->json([
            'message' => $doctor
        ],200);
    }
}
