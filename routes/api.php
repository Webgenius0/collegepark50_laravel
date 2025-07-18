<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\React\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\React\User\Auth\AuthenticationController;




Route::group(['middleware' => 'guest:api'], function () {

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



Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::post('/update-role', [AuthenticationController::class, 'updateRole']);

});
