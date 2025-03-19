<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('savepatient', 'RegistrationController@createUserFromInvitation')->name('savePatient');

Route::get('docprofile/{id}',[QrCodeController::class,'readQRcode']);

Route::get('successfulInvitation', 'RegistrationController@showInvitationSuccessPage');

Route::get('invitation/{hash}', 'RegistrationController@invitationConfirmation');

Route::post('savepatient', 'RegistrationController@createUserFromInvitation');