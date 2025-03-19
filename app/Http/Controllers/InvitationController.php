<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoctorPatientList;

class InvitationController extends Controller
{
    public function index(){
        $list = DoctorPatientList::orderBy('date_added', 'desc')->with('doctor')->get();
        return response()->json($list, 200);
    }
}
