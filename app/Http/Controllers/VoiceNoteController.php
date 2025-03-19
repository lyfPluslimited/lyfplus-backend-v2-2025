<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VoiceNoteController extends Controller
{
    public function store(Request $request){
        //value to be voicenote

        if($request->hasFile('voicenote')){
            $filename = time().'.'.$request->voicenote->getClientOriginalExtension();
            $request->voicenote->move(public_path('images/voice'), $filename);

            return response()->json('http://167.172.12.18/app/public/images/voice/'.$filename
            , 200);
        }

    }

    public function retrieve(Request $request){
        //value to be voicenote

        return response()->json("http://167.172.12.18/app/public/images/voice/$request->voicenote", 200);
    }
}
