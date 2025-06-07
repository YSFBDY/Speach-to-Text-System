<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;

use Illuminate\Http\Request;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPassRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\GenerateOtpRequest;

use App\Mail\ResetPassword; 

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class AuthController extends Controller
{
    public function Register(RegisterRequest $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                "message" => "Email already exists"
            ], 400); // 409 Conflict
        }
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role?:"user"
        ]);

        $token = $user->createToken($user->email)->plainTextToken;

        return response()->json([
            'token' => $token,
            "user_id" => $user->user_id,
        ], 201);
    }


    public function Login(LoginRequest $request) {

        $user = User::where("email", $request->email)->first();

        // Check if user exists and the password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([ 
                "message" => "Incorrect data"
            ], 401); // 401 Unauthorized
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user_id' => $user->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role
        ]);


    }




    public function Logout(Request $request) {
        $user_id = $request->input('user_id');
    
        if (!$user_id) {
            return response()->json(["message" => "Missing user_id"], 404);
        }
    
        $user = User::find($user_id);
    
        if (!$user) {
            return response()->json(["message" => "User not found"], 404);
        }
    
        $user->tokens()->delete(); // revoke all tokens
        return response()->json(["message" => "Logged out successfully"]);
    }
    





    public function GenerateOtp(GenerateOtpRequest $request) {
       
        if (User::where('email', $request->email)->doesntExist()) {
            return response()->json(["message" => "Invalid email"], 404);
        }
       

        $otp = rand(100000, 999999);

        Otp::updateOrCreate(
            ['email' => $request->email], // If OTP already exists, update it
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(30)]
        );

        Mail::to($request->email)->send(new ResetPassword($otp));

        return response()->json(["message" => "OTP sent to your email."]);
       
        
    }



    public function verifyOtp(VerifyOtpRequest $request) {

        $otpRecord = Otp::where('email', $request->email)
                        ->where('otp', $request->otp)
                        ->where('expires_at', '>', now())
                        ->first();
    
        if (!$otpRecord) {
            return response()->json(["message" => "Invalid or expired OTP"], 400);
        }
    
        // OTP is valid, delete it after use
        $otpRecord->delete();
    
        return response()->json(["message" => "OTP verified successfully"]);
    }




    public function ResetPassword(ResetPassRequest $request) {

        if (User::where('email', $request->email)->doesntExist()) {
            return response()->json(["message" => "Invalid email"], 404);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);
    
        return response()->json(["message" => "Password updated successfully"]);

    }




}
