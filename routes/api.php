<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\React\Auth\SocialLoginController;
use App\Http\Controllers\Api\React\Auth\UserProfileController;
use App\Http\Controllers\Api\React\Auth\AuthenticationController;
use App\Http\Controllers\Api\React\User\Auth\ResetPasswordController;

//health-check
Route::get("/check", function () {
    return "Project running!";
});


Route::group(['middleware' => 'guest:api'], function () {
    // Authentication
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/register-otp-verify', [AuthenticationController::class, 'RegistrationVerifyOtp']);

    // Password Reset
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
    Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp']);


    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});


Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::put('/update-role', [AuthenticationController::class, 'updateRole']);

    Route::get('/profile', [UserProfileController::class, 'profile']);
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']);
    Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']);
});
