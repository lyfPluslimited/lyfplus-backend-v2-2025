<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="LyfPlus API Documentation",
 *      description="API documentation for Laravel 12",
*      @OA\Contact(
 *          email="kmisigaro@outlook.com"
 *      ),
 * )
 */
class AuthController extends Controller
{
     /**
     * @OA\Get(
     *      path="/api/login",
     *      operationId="login",
     *      tags={"Login"},
     *      summary="Login User",
     *      description="Returns example data",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function login(Request $request){

        $checkIfUserExists = User::where('email', $request->email)->exists();

        if($checkIfUserExists){
            $user = User::where('email', $request->email)->first();

            if(Hash::check($request->password, $user->password)){
                return response()->json(['user' => $user, 'token' => $user->createToken('123456')->plainTextToken], 200);
            }

            return response()->json('Password is incorrect', 401);
        }

        return response()->json('User not found', 401);
    }

    public function loginWithPhone(Request $request){
        $checkIfUserExists = User::where('phone', $request->phone)->exists();

        if($checkIfUserExists){
            $user = User::where('phone', $request->phone)->first();

            if(Hash::check($request->password, $user->password)){
                return response()->json(['user' => $user ], 200);
            }

            return response()->json('Password is incorrect', 401);
        }

        return response()->json('User not found', 401);
	}

    public function getDoctor($doctorID){
        return response()->json(User::where('userID', $doctorID)->first(), 200);
    }
}


