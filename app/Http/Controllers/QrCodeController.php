<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\ReferralLink;

class QrCodeController extends Controller
{
    public function generateQRCode($id){

        //get or generate referral link 
        $referralLink = ReferralLink::firstOrCreate(
            ['doctor_id' => $id], 
            ['link' => time() ]
        );

        //get user
        $doctor = User::where('userID', $id)->first();

        //generate QR Code
        if ($doctor->qrcode == null) {
            \QrCode::size(100)
                ->format('svg')
                ->generate('http://www.lyfplus.co.tz/app/public/docprofile/'.$id, 'images/'.$doctor->firstName.$doctor->lastName.'.svg');

            $doctor->update([
                'qrcode' => '/images/'.$doctor->firstName.$doctor->lastName.'.svg'
            ]);

            return response()->json('QR Code generated', 200);

        } else{
            return response()->json('QR Code already exists', 200);
        }

    }


    public function readQRcode($id){

        $doctor = User::where('userID', $id)->with(['specialization','link'])->first();
        
        return view('docProfile', \compact('doctor'));
    }

}
