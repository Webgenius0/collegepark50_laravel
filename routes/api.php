<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\React\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\React\User\Auth\UserProfileController;
use App\Http\Controllers\Api\React\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\React\User\Auth\AuthenticationController;




Route::group(['middleware' => 'guest:api'], function () {

    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::post('/register', [AuthenticationController::class, 'register']);
    Route::post('/register-otp-verify', [AuthenticationController::class, 'RegistrationVerifyOtp']);

    // Password Reset
    Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'verifyOTP']);
    Route::post('/resend-otp', [ResetPasswordController::class, 'resendOtp']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);

    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});



Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/update-role', [AuthenticationController::class, 'updateRole']);
    Route::get('/profile', [UserProfileController::class, 'profile']);
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']);
    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);

});

