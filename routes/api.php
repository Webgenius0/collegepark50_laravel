<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Post\PostController;
use App\Http\Controllers\Api\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\User\Profile\UserProfileController;
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
    // Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth'], function () {
    // logout
    Route::post('/logout', [AuthenticationController::class, 'logout'])->middleware('auth');

    //role update
    Route::put('/update-role', [AuthenticationController::class, 'updateRole'])->middleware('auth');


    // User Profile and avatar
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [UserProfileController::class, 'show']);
        Route::post('/update', [UserProfileController::class, 'update']);
        Route::put('/notification-status', [UserProfileController::class, 'status']);
        Route::post('/location', [UserProfileController::class, 'location']);
    });

    //Post routes
    Route::group(['prefix' => 'post'], function () {
        Route::post('/store', [PostController::class, 'store']);    // Create new post
        Route::get('/all', [PostController::class, 'index']);       // Fetch all posts
        Route::get('/show/{id}', [PostController::class, 'show']);       // Single post view
        Route::delete('/delete/{id}', [PostController::class, 'destroy']); // Delete post
    });
});
