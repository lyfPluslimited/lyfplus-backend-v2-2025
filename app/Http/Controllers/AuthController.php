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
     *      description="Allows a user to log in using email and password. Returns a token upon successful login.",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(type="string", format="email")
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(type="string", format="password")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login. Returns user data and token.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
     *              @OA\Property(property="token", type="string")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. Incorrect password or user not found."
     *      )
     * )
     */
    public function login(Request $request)
    {
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

    /**
     * @OA\Get(
     *      path="/api/login/phone",
     *      operationId="loginWithPhone",
     *      tags={"Login"},
     *      summary="Login User with Phone Number",
     *      description="Allows a user to log in using their phone number and password.",
     *      @OA\Parameter(
     *          name="phone",
     *          in="query",
     *          required=true,
     *          @OA\Schema(type="string", format="phone")
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(type="string", format="password")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login. Returns user data.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. Incorrect password or user not found."
     *      )
     * )
     */
    public function loginWithPhone(Request $request)
    {
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

    /**
     * @OA\Get(
     *      path="/api/doctor/{doctorID}",
     *      operationId="getDoctor",
     *      tags={"Doctor"},
     *      summary="Get Doctor Information",
     *      description="Fetches details of a doctor by their user ID.",
     *      @OA\Parameter(
     *          name="doctorID",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation. Returns doctor details.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Doctor not found."
     *      )
     * )
     */
    public function getDoctor($doctorID)
    {
        return response()->json(User::where('userID', $doctorID)->first(), 200);
    }
}