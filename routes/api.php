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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SelcomPaymentController;
use App\Http\Controllers\UserHistoryController;
use App\Http\Controllers\VoiceNoteController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\SecondForumController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\SubscriptionController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', function() {
    return 'test';
});

Route::group(['prefix' => 'register'], function () {
    Route::post('user', [RegistrationController::class, 'createUser']);
    Route::post('specialist', [RegistrationController::class, 'createSpecialist']);
});

Route::group(['prefix' => 'subscription'], function() {
    Route::post('subscribe', [ReferralController::class, 'subscribe']);
    Route::get('{userID}', [ReferralController::class, 'getSubscriptions']);
    Route::post('check', [ReferralController::class, 'doctorSubscription']);
    Route::get('/', [ReferralController::class, 'subscribers']);
});

Route::group(['prefix' => 'update'], function() {
    Route::post('user', [RegistrationController::class, 'updateUserProfile']);
});

Route::get('getDoctor/{userID}', [DoctorAppController::class, 'getDoctor']);

Route::post('updateDoctor', [RegistrationController::class, 'updateDoctorProfile']);
Route::post('updateDoctorImage', [RegistrationController::class, 'updateDoctorImage']);

Route::post('login-admin', [AdminController::class, 'loginAdmin']);

Route::group(['prefix' => 'doctor-app'], function() {
    Route::get('countries', [DoctorAppController::class, 'getCountries']);
    Route::get('regions', [DoctorAppController::class, 'getRegions']);
});

//****************************G PAY ID GENERATION*******************/
Route::get('generate-payment-ids-script', [SubscriptionController::class, 'generateConsultationPaymentScript']);
Route::get('generate-new-payment-ids-script', [SubscriptionController::class, 'userToConsultationScript']);
//****************************END G PAY ID GENERATION*******************/

Route::group(['prefix' => 'chat'], function() {
    Route::post('store', [ChatController::class, 'storeChat']);
    Route::post('updateSessionTime', [ChatController::class, 'updateSessionTime']);
    Route::get('getDoctorChatHistory/{id}', [ChatController::class, 'getDoctorChatHistory']);
    Route::get('getPatientChatHistory/{id}', [ChatController::class, 'getPatientChatHistory']);
    Route::post('endSession', [ChatController::class, 'endSession']);
});

//******** INCENTIVE MODULE **********
Route::get('incentive/{id}', [IncentiveController::class, 'changeIncentiveStatus']);
Route::post('onlinetime', [IncentiveController::class, 'onlineStatus']);
Route::post('storeKPI', [IncentiveController::class, 'storeKpi']);
Route::get('displayKPIs', [IncentiveController::class, 'displayKPIs']);
Route::post('storeKpiPrices/{id}', [IncentiveController::class, 'setKpiPrices']);
Route::get('getIncentiveDoctors', [IncentiveController::class, 'displayDoctors']);
Route::delete('deleteKpi/{id}', [IncentiveController::class, 'deleteKpi']);

Route::get('doctorKpiPrices/{id}', [IncentiveController::class, 'getKpiPriceforDoctor']);
Route::post('updateDoctorKpiPrices/{id}', [IncentiveController::class, 'updateKPIDoctorPrices']);

Route::post('updateKpiQuantity', [IncentiveController::class, 'updateKpiQuantity']);

Route::get('getDoctorUnpaidIncentive/{id}', [IncentiveController::class, 'getDoctorUnpaidAmount']);
Route::get('getDoctorTrackingKpis/{doctorId}', [IncentiveController::class, 'getDoctorKpis']);
Route::get('getGeneralTrackings/{doctorId}', [IncentiveController::class, 'getGeneralKpis']);
Route::post('getTrackingsOnDays/{doctorId}', [IncentiveController::class, 'getKpisInRange']);

Route::get('getGeneralTrackingsMobile/{doctorId}', [IncentiveController::class, 'getGeneralKpisMobile']);
Route::post('getTrackingsOnDaysMobile/{doctorId}', [IncentiveController::class, 'getKpisInRangeMobile']);

Route::get('incentivePayment/{doctorId}', [IncentiveController::class, 'makeIncentivePayment']);

Route::get('doctorKPIprices/{doctorId}', [IncentiveController::class, 'doctorPrices']);
//*********** END OF INCENTIVE MODULE */

Route::get('generateQRCode/{id}', [QrCodeController::class, 'generateQRCode']);

Route::post('consultationAlert', [AdminController::class, 'consultationHistory']);

Route::group(['prefix' => 'swahilies'], function() {
    Route::post('payment', [SwahiliesPayController::class, 'makePayment']);
    Route::post('callback', [SwahiliesPayController::class, 'callback']);
    Route::get('paymentList', [SwahiliesPayController::class, 'paymentList']);
    Route::get('callbackList', [SwahiliesPayController::class, 'callbackList']);
    Route::get('checkCallback/{orderID}', [SwahiliesPayController::class, 'checkOrder']);
});

Route::get('users', [AdminController::class, 'getUsers']);

Route::group(['prefix' => 'admin'], function() {
    Route::get('users', [AdminController::class, 'getUsers']);
    Route::get('abuse-reports', [AdminController::class, 'getAbuseReports']);
    Route::get('get-comments', [AdminController::class, 'getUserComments']);
    Route::get('get-posts', [AdminController::class, 'getPosts']);
    Route::get('single-post/{id}', [AdminController::class, 'getSinglePost']);
    Route::get('getDoctorPayments/{id}', [AdminController::class, 'getDoctorTransaction']);
    Route::get('get-selcom-payments', [AdminController::class, 'getSelcomPayments']);
    Route::post('getUser/{userID}', [AdminController::class, 'getSingleUser']);
    Route::post('savePost', [AdminController::class, 'savePost']);

    //consultation routes
    Route::get('get-consultation-period', [AdminController::class, 'getConsultationPeriod']);
    Route::patch('update-consultation', [AdminController::class, 'updateConsultationPeriod']);

    //specialization Routes
    Route::get('specialization', [AdminController::class, 'getSpecializations']);
    Route::post('save-specialization', [AdminController::class, 'saveSpecialization']);
    Route::delete('delete-specialization/{id}', [AdminController::class, 'deleteSpecialization']);
    Route::patch('update-specialization', [AdminController::class, 'updateSpecialization']);

    //symptom routes
    Route::get('symptoms', [AdminController::class, 'getSymptoms']);
    Route::delete('delete-symptom/{id}', [AdminController::class, 'deleteSymptom']);
    Route::post('save-symptom', [AdminController::class, 'saveSymptom']);

    //Hospital routes
    Route::get('hospital', [AdminController::class, 'getHospitals']);
    Route::post('save-hospital', [AdminController::class, 'saveHospital']);
    Route::patch('hospitalStatus/{id}', [AdminController::class, 'changeHospitalStatus']);
    Route::patch('update-hospital', [AdminController::class, 'updateHospital']);
    Route::get('getSingleHospital/{id}', [AdminController::class, 'getSingleHospital']);
    Route::delete('delete-hospital/{id}', [AdminController::class, 'deleteHospital']);

    //Topic routes
    Route::get('topics', [AdminController::class, 'getTopics']);
    Route::get('getSingleTopic/{id}', [AdminController::class, 'getSingleTopic']);
    Route::post('saveTopics', [AdminController::class, 'saveTopic']);
    Route::delete('deleteTopic/{id}', [AdminController::class, 'deleteTopic']);

    //Messages
    Route::get('get-messages', [AdminController::class, 'getFastHubMessages']);

    //Article routes
    Route::get('getAllArticles', [AdminController::class, 'getArticles']);
    Route::get('getSingleArticle/{id}', [AdminController::class, 'getSingleArticle']);
    Route::get('getFullTopicDetails', [AdminController::class, 'getTopicss']);
    Route::post('saveArticle', [AdminController::class, 'saveArticle']);
    Route::patch('updateArticle', [AdminController::class, 'updateArticle']);
    Route::delete('deleteArticle/{id}', [AdminController::class, 'deleteArticle']);

    //client routes
    Route::post('delete-client/{userID}', [AdminController::class, 'deleteClient']);

    //Doctor routes
    Route::patch('update-doctor', [AdminController::class, 'updateDoctorDetails']);
    Route::post('delete-doctor/{userID}', [AdminController::class, 'deleteDoctor']);
    Route::get('getDocs', [AdminController::class, 'getDoctors']);
    Route::patch('saveFee', [AdminController::class, 'saveDoctorFee']);

    //Patient Invitation routes
    Route::get('invitations', [InvitationController::class, 'index']);
});

Route::post('sendMessage', [RegistrationController::class, 'sendMessageSMS']);

Route::group(['prefix' => 'selcom'], function() {
    Route::post('payment', [SelcomPaymentController::class, 'processPayment']);
    Route::post('USSDpayment', [SelcomPaymentController::class, 'processUSSDPayment']);
    Route::post('checkPaymentStatus', [SelcomPaymentController::class, 'checkSelcomPaymentStatus']);
    Route::post('callback', [SelcomPaymentController::class, 'USSDcallback']);
    Route::post('otherMobilePayment', [SelcomPaymentController::class, 'processOtherMobilePayment']);
    Route::post('cardPayment', [SelcomPaymentController::class, 'processCardPayment']);
    Route::post('resend/payment', [SelcomPaymentController::class, 'resendUSSDOrder']);
    Route::post('deleteOrder', [SelcomPaymentController::class, 'deleteOrder']);
});

Route::post('user-history', [UserHistoryController::class, 'getUserHistory']);
Route::post('storeUserHistory', [UserHistoryController::class, 'storeUserHistory']);
Route::get('getUserHistory/{userID}/{specialistID}', [UserHistoryController::class, 'getPatientSpecialistHistory']);

Route::post('login', [AuthController::class, 'login']);

Route::post('send-image', [ChatController::class, 'saveImage']);

Route::get('get-experience', [UserHistoryController::class, 'getExperiencePrices']);

Route::post('verify-specialist', [RegistrationController::class, 'verification']);

Route::post('unverify-specialist', [RegistrationController::class, 'unverify']);

Route::post('register-patient', [RegistrationController::class, 'registerPatient']);

Route::post('getID', [RegistrationController::class, 'getUserObject']);

Route::post('getUserDetails', [RegistrationController::class, 'getUserDetails']);

Route::group(['prefix' => 'search'], function() {
    Route::post('specialistName', [RegistrationController::class, 'searchSpecialistName']);
    Route::post('specialization', [RegistrationController::class, 'searchSpecialSpeciality']);
});

Route::group(['prefix' => 'referral'], function() {
    Route::get('/', [ReferralController::class, 'getAllReferralCodes']);
    Route::post('createLink/{userID}', [ReferralController::class, 'createReferralLink']);
    Route::post('addReferral', [ReferralController::class, 'addReferral']);
    Route::get('favoriteDoctor/{userID}', [ReferralController::class, 'getFavoriteDoctor']);
});

Route::group(['prefix' => 'voicenote'], function() {
    Route::post('store', [VoiceNoteController::class, 'store']);
    Route::get('retrieve', [VoiceNoteController::class, 'retrieve']);
});

Route::get('forumBasedOffUser/{id}', [SecondForumController::class, 'getForumBasedOfUser']);

Route::group(['prefix' => 'forum'], function() {
    Route::get('/', [ForumController::class, 'getAllForums']);
    Route::get('/docroom', [ForumController::class, 'getDoctorRoomForum']);
    Route::post('/save', [ForumController::class, 'createForumPost']);
    //forum routes
    Route::get('generate-slugs', [ForumController::class, 'slugGenerator']);
    //display post to site
    Route::get('post/{id}', [ForumController::class, 'getForumPost']);
    Route::get('postForApi/{id}', [ForumController::class, 'getPostForAPI']);
    //comment
    Route::post('comment', [ForumController::class, 'comment']);
    Route::get('getForumComments/{id}', [ForumController::class, 'forumComments']);
    Route::post('like/{id}', [ForumController::class, 'likePost']);
    Route::get('likes', [ForumController::class, 'forumLikes']);
    // Route::get('getForumByTopic/{id}', [ForumController::class, 'getForumByTopic']);
});

Route::group(['prefix' => 'services'], function() {
    Route::get('/', [HomeServicesController::class, 'getServices']);
    Route::get('doctors/{id}', [HomeServicesController::class, 'doctorsFromService']);
    Route::post('add', [HomeServicesController::class, 'addService']);
    Route::post('add/doctor', [HomeServicesController::class, 'addDoctorToService']);
    Route::post('add/doctor/app', [HomeServicesController::class, 'addDoctorToServiceApp']);
    Route::post('update/{id}', [HomeServicesController::class, 'updateService']);
    Route::delete('delete/doctor/{id}', [HomeServicesController::class, 'removeDoctorFromService']);
    Route::delete('delete/{id}', [HomeServicesController::class, 'deleteService']);
    Route::post('confirm', [HomeServicesController::class, 'confirmService']);
    Route::get('visits', [HomeServicesController::class, 'confirmedRequests']);
    Route::get('approvalstatus/{homeserviceid}/{doctorid}', [HomeServicesController::class, 'doctorApproval']);
    Route::get('doctor/{id}', [HomeServicesController::class, 'getDoctorServices']);
});

Route::get('get/slots', [DoctorAppController::class, 'getAppointments']);
Route::post('create/slots', [DoctorAppController::class, 'createSlots']);