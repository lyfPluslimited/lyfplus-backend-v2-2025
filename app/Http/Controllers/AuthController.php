<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     operationId="loginNormally",
     *     description="Authenticate a user by email and password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",

     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password is incorrect or User not found")
     *         )
     *     )
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
     * @OA\Post(
     *      path="/api/auth/login/phone",
     *      operationId="loginWithPhone",
     *      tags={"Authentication"},
     *      summary="Login user with phone number",
     *      description="Authenticate user using phone number and password",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"phone","password"},
     *              @OA\Property(property="phone", type="string"),
     *              @OA\Property(property="password", type="string", format="password")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login",
     *          @OA\JsonContent(
     *              @OA\Property(property="user", ref="#/components/schemas/User")
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
