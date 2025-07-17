<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\User\Auth\AuthenticationController;

//health-check
Route::get("/check", function () {
    return "Project running!";
});

/*
|--------------------------------------------------------------------------
| Guest Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'guest:api'], function () {
    // Authentication
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/register-otp-verify', [AuthenticationController::class, 'RegistrationVerifyOtp']);

    // Password Reset
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);

    // Social Login
    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});

// logout
Route::post('/logout', [AuthenticationController::class, 'logout']);

//role update
Route::put('/update-role', [AuthenticationController::class, 'updateRole']);
