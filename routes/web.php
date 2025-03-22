<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RegistrationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group that
| contains the "web" middleware group. Now create something great!
|
*/

// Home route
Route::get('/', fn () => view('welcome'));

// Registration routes
Route::controller(RegistrationController::class)->group(function () {
    Route::post('savepatient', 'createUserFromInvitation')->name('savePatient');
    Route::get('successfulInvitation', 'showInvitationSuccessPage');
    Route::get('invitation/{hash}', 'invitationConfirmation');
});

// QR Code route
Route::get('docprofile/{id}', [QrCodeController::class, 'readQRcode']);