<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranscriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TranslationController; 
use App\Http\Controllers\SubscribtionController;
use App\Http\Controllers\PersonalInfoController;

use App\Http\Middleware\AccessToTranscriptionMiddleware;
use App\Http\Middleware\AccessToTranslationMiddleware;
use App\Http\Middleware\TokenAuthMiddleware;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//->middleware(TokenAuthMiddleware::class)


Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'Register');
    Route::post('login', 'Login');
    Route::post('logout', 'Logout');
    Route::post('generate-otp', 'GenerateOtp');
    Route::post('verify-otp', 'verifyOtp');
    Route::post('reset-password', 'ResetPassword');
});



Route::controller(ScreenController::class)->middleware(TokenAuthMiddleware::class)->group(function () {
    Route::post('create-screen', 'createscreen');
    Route::post('leave-feedback', 'leavefeedback');
    Route::get('show-screens/{user_id}', 'showscreens');
    Route::get('show-screen/{screen_id}', 'showscreen');
    Route::delete('delete-screen/{screen_id}', 'deletescreen');
});






Route::controller(TranslationController::class)->middleware(TokenAuthMiddleware::class)->group(function () {
    Route::post('translate', 'translate')->middleware(AccessToTranslationMiddleware::class);
});





Route::controller(TranscriptionController::class)->middleware(TokenAuthMiddleware::class)->group(function () {
    Route::post('transcribe', 'transcribe')->middleware(AccessToTranscriptionMiddleware::class);
});




Route::controller(PersonalInfoController::class)->middleware(TokenAuthMiddleware::class)->group(function () {
    Route::get('show-limits/{user_id}', 'showlimits');
});





Route::controller(PlanController::class)->middleware(TokenAuthMiddleware::class)->group(function () {
    Route::post('create-plan', 'createplan');
    Route::get('show-plans', 'showplans');
});




Route::controller(SubscribtionController::class)->middleware(TokenAuthMiddleware::class)->group(function () {
    Route::post('payment-process', 'paymentprocess');
    Route::get('get-payment-status/{payment_id}', 'getPaymentStatus');
    Route::get('show-payments/{user_id}', 'showpayments');
    Route::get('show-subscription/{user_id}', 'showsubscriptions');
});