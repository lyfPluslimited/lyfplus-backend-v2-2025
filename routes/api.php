<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\DoctorAppController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\IncentiveController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SwahiliesPayController;
use App\Http\Controllers\HomeServicesController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', function(){
    return 'test';
});

Route::group(['prefix' => 'register'],function () {
    Route::post('user','RegistrationController@createUser');
    Route::post('specialist', 'RegistrationController@createSpecialist');
});

Route::group(['prefix' => 'subscription'], function(){
    Route::post('subscribe', [ReferralController::class, 'subscribe']);
    Route::get('{userID}', [ReferralController::class,'getSubscriptions']);
    Route::post('check',[ReferralController::class, 'doctorSubscription']);
    Route::get('/', [ReferralController::class,'subscribers']);
});

Route::group(['prefix' => 'update'], function(){
    Route::post('user', 'RegistrationController@updateUserProfile');
});

Route::get('getDoctor/{userID}', [DoctorAppController::class,'getDoctor']);

Route::post('updateDoctor', 'RegistrationController@updateDoctorProfile');
Route::post('updateDoctorImage', 'RegistrationController@updateDoctorImage');

Route::post('login-admin', 'AdminController@loginAdmin');

Route::group(['prefix' => 'doctor-app'], function(){
    Route::get('countries','DoctorAppController@getCountries');
    Route::get('regions','DoctorAppController@getRegions');
});

//****************************G PAY ID GENERATION*******************/
Route::get('generate-payment-ids-script', 'SubscriptionController@generateConsultationPaymentScript');
Route::get('generate-new-payment-ids-script','SubscriptionController@userToConsultationScript');
//****************************END G PAY ID GENERATION*******************/



Route::group(['prefix' => 'chat'], function(){

    Route::post('store', [ChatController::class,'storeChat']);
    Route::post('updateSessionTime', [ChatController::class,'updateSessionTime']);
    Route::get('getDoctorChatHistory/{id}', [ChatController::class,'getDoctorChatHistory']);
    Route::get('getPatientChatHistory/{id}', [ChatController::class,'getPatientChatHistory']);
    Route::post('endSession', [ChatController::class,'endSession']);

});

//******** INCENTIVE MODULE **********
Route::get('incentive/{id}','IncentiveController@changeIncentiveStatus');
Route::post('onlinetime','IncentiveController@onlineStatus');
Route::post('storeKPI','IncentiveController@storeKpi');
Route::get('displayKPIs','IncentiveController@displayKPIs');
Route::post('storeKpiPrices/{id}','IncentiveController@setKpiPrices');
Route::get('getIncentiveDoctors','IncentiveController@displayDoctors');
Route::delete('deleteKpi/{id}','IncentiveController@deleteKpi');

Route::get('doctorKpiPrices/{id}',[IncentiveController::class,'getKpiPriceforDoctor']);
Route::post('updateDoctorKpiPrices/{id}',[IncentiveController::class,'updateKPIDoctorPrices']);

Route::post('updateKpiQuantity',[IncentiveController::class,'updateKpiQuantity']);

Route::get('getDoctorUnpaidIncentive/{id}','IncentiveController@getDoctorUnpaidAmount');
Route::get('getDoctorTrackingKpis/{doctorId}',[IncentiveController::class, 'getDoctorKpis']);
Route::get('getGeneralTrackings/{doctorId}',[IncentiveController::class,'getGeneralKpis']);
Route::post('getTrackingsOnDays/{doctorId}',[IncentiveController::class,'getKpisInRange']);

Route::get('getGeneralTrackingsMobile/{doctorId}',[IncentiveController::class,'getGeneralKpisMobile']);
Route::post('getTrackingsOnDaysMobile/{doctorId}',[IncentiveController::class,'getKpisInRangeMobile']);

Route::get('incentivePayment/{doctorId}',[IncentiveController::class,'makeIncentivePayment']);

Route::get('doctorKPIprices/{doctorId}',[IncentiveController::class,'doctorPrices']);
//*********** END OF INCENTIVE MODULE */

Route::get('generateQRCode/{id}',[QrCodeController::class,'generateQRCode']);

Route::post('consultationAlert','AdminController@consultationHistory');

Route::group(['prefix' => 'swahilies'], function(){
    Route::post('payment',[SwahiliesPayController::class, 'makePayment']);
    Route::post('callback',[SwahiliesPayController::class, 'callback']);
    Route::get('paymentList',[SwahiliesPayController::class,'paymentList']);
    Route::get('callbackList',[SwahiliesPayController::class,'callbackList']);
    Route::get('checkCallback/{orderID}',[SwahiliesPayController::class,'checkOrder']);
});

Route::get('users',[AdminController::class, 'getUsers']);

Route::group(['prefix' => 'admin'], function(){
    Route::get('users','AdminController@getUsers');
    
    Route::get('abuse-reports', 'AdminController@getAbuseReports');

    Route::get('get-comments', 'AdminController@getUserComments');

    Route::get('get-posts', 'AdminController@getPosts');

    Route::get('single-post/{id}', 'AdminController@getSinglePost');

    Route::get('getDoctorPayments/{id}', 'AdminController@getDoctorTransaction');

    Route::get('get-selcom-payments', 'AdminController@getSelcomPayments');

    Route::post('getUser/{userID}', 'AdminController@getSingleUser');

    Route::post('savePost', 'AdminController@savePost');

    //consultation routes
    Route::get('get-consultation-period', 'AdminController@getConsultationPeriod');
    Route::patch('update-consultation', 'AdminController@updateConsultationPeriod');

    //specialization Routes
    Route::get('specialization', 'AdminController@getSpecializations');
    Route::post('save-specialization', 'AdminController@saveSpecialization');
    Route::delete('delete-specialization/{id}', 'AdminController@deleteSpecialization');
    Route::patch('update-specialization', 'AdminController@updateSpecialization');

    //symptom routes
    Route::get('symptoms','AdminController@getSymptoms');
    Route::delete('delete-symptom/{id}', 'AdminController@deleteSymptom');
    Route::post('save-symptom','AdminController@saveSymptom');

    //Hospital routes
    Route::get('hospital','AdminController@getHospitals');
    Route::post('save-hospital', 'AdminController@saveHospital');
    Route::patch('hospitalStatus/{id}','AdminController@changeHospitalStatus');
    Route::patch('update-hospital','AdminController@updateHospital' );
    Route::get('getSingleHospital/{id}', 'AdminController@getSingleHospital');
    Route::delete('delete-hospital/{id}', 'AdminController@deleteHospital');

    //Topic routes
    Route::get('topics','AdminController@getTopics');
    Route::get('getSingleTopic/{id}', 'AdminController@getSingleTopic');
    Route::post('saveTopics', 'AdminController@saveTopic');
    Route::delete('deleteTopic/{id}', 'AdminController@deleteTopic');

    //Messages
    Route::get('get-messages', 'AdminController@getFastHubMessages');

    //Article routes
    Route::get('getAllArticles', 'AdminController@getArticles');
    Route::get('getSingleArticle/{id}', 'AdminController@getSingleArticle');
    Route::get('getFullTopicDetails', 'AdminController@getTopicss');
    Route::post('saveArticle','AdminController@saveArticle');
    Route::patch('updateArticle','AdminController@updateArticle');
    Route::delete('deleteArticle/{id}', 'AdminController@deleteArticle');

    //client routes
    Route::post('delete-client/{userID}', 'AdminController@deleteClient');

    //Doctor routes
    Route::patch('update-doctor', 'AdminController@updateDoctorDetails');
    Route::post('delete-doctor/{userID}', 'AdminController@deleteDoctor');
    Route::get('getDocs','AdminController@getDoctors');
    Route::patch('saveFee', 'AdminController@saveDoctorFee');

    //Patient Invitation routes
    Route::get('invitations','InvitationController@index');
  
});

Route::post('sendMessage', 'RegistrationController@sendMessageSMS');

Route::group(['prefix' => 'selcom'], function(){
    Route::post('payment', 'SelcomPaymentController@processPayment');
    Route::post('USSDpayment', 'SelcomPaymentController@processUSSDPayment');
    Route::post('checkPaymentStatus', 'SelcomPaymentController@checkSelcomPaymentStatus');
    Route::post('callback', 'SelcomPaymentController@USSDcallback');

    Route::post('otherMobilePayment', 'SelcomPaymentController@processOtherMobilePayment');
    Route::post('cardPayment', 'SelcomPaymentController@processCardPayment');

    Route::post('resend/payment', 'SelcomPaymentController@resendUSSDOrder');

    Route::post('deleteOrder', 'SelcomPaymentController@deleteOrder');
}); 

Route::post('user-history', 'UserHistoryController@getUserHistory');
Route::post('storeUserHistory', 'UserHistoryController@storeUserHistory');
Route::get('getUserHistory/{userID}/{specialistID}', 'UserHistoryController@getPatientSpecialistHistory');

Route::post('login', 'AuthController@login');

Route::post('send-image', 'ChatController@saveImage');

Route::get('get-experience', 'UserHistoryController@getExperiencePrices');

Route::post('verify-specialist', 'RegistrationController@verification');

Route::post('unverify-specialist', 'RegistrationController@unverify');

Route::post('register-patient','RegistrationController@registerPatient');

Route::post('getID','RegistrationController@getUserObject');

Route::post('getUserDetails', 'RegistrationController@getUserDetails');

Route::group(['prefix' => 'search'],function () {
    Route::post('specialistName','RegistrationController@searchSpecialistName');
    Route::post('specialization', 'RegistrationController@searchSpecialSpeciality');
});

Route::group(['prefix' => 'referral'],  function(){
    Route::get('/',[ReferralController::class, 'getAllReferralCodes']);
    Route::post('createLink/{userID}', [ReferralController::class,'createReferralLink']);
    Route::post('addReferral',[ReferralController::class,'addReferral']);
    Route::get('favoriteDoctor/{userID}',[ReferralController::class,'getFavoriteDoctor']);
});

Route::group(['prefix' => 'voicenote'], function(){
    Route::post('store','VoiceNoteController@store');
    Route::get('retrieve','VoiceNoteController@retrieve');
});

Route::get('forumBasedOffUser/{id}','SecondForumController@getForumBasedOfUser');

Route::group(['prefix' => 'forum'], function(){
    Route::get('/', 'ForumController@getAllForums');
    Route::get('/docroom', 'ForumController@getDoctorRoomForum');
    Route::post('save', 'ForumController@createForumPost');
    //forum routes
    Route::get('generate-slugs', 'ForumController@slugGenerator');
    //display post to site
    Route::get('post/{id}', 'ForumController@getForumPost');
    Route::get('postForApi/{id}','ForumController@getPostForAPI');
    //comment
    Route::post('comment','ForumController@comment');
    Route::get('getForumComments/{id}','ForumController@forumComments');
    Route::post('like/{id}','ForumController@likePost');
    Route::get('likes'.'ForumController@forumLikes');
   // Route::get('getForumByTopic/{id}', 'ForumController@getForumByTopic');
});

Route::group(['prefix' => 'services'], function(){
    Route::get('/', 'HomeServicesController@getServices');
    Route::get('doctors/{id}', 'HomeServicesController@doctorsFromService');
    Route::post('add', 'HomeServicesController@addService');
    Route::post('add/doctor', 'HomeServicesController@addDoctorToService');
    Route::post('add/doctor/app', 'HomeServicesController@addDoctorToServiceApp');
    Route::post('update/{id}', 'HomeServicesController@updateService');
    Route::delete('delete/doctor/{id}', 'HomeServicesController@removeDoctorFromService');
    Route::delete('delete/{id}', 'HomeServicesController@deleteService');
    Route::post('confirm', 'HomeServicesController@confirmService');
    Route::get('visits', 'HomeServicesController@confirmedRequests');
    Route::get('approvalstatus/{homeserviceid}/{doctorid}','HomeServicesController@doctorApproval');
    Route::get('doctor/{id}', [HomeServicesController::class,'getDoctorServices']);
});

Route::get('get/slots', 'DoctorAppController@getAppointments');
Route::post('create/slots', 'DoctorAppController@createSlots');
