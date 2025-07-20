<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\React\Post\PostController;
use App\Http\Controllers\Api\React\Post\PostLikeController;
use App\Http\Controllers\Api\React\Post\PostShareController;
use App\Http\Controllers\Api\React\Auth\SocialLoginController;
use App\Http\Controllers\Api\React\CMS\EventController;
use App\Http\Controllers\Api\React\CMS\FeatureController;
use App\Http\Controllers\Api\React\CMS\HomeController;
use App\Http\Controllers\Api\React\CMS\NewsletterController;
use App\Http\Controllers\Api\React\Post\PostCommentController;
use App\Http\Controllers\Api\React\Post\PostCommentReplyController;
use App\Http\Controllers\Api\React\User\Auth\UserProfileController;
use App\Http\Controllers\Api\React\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\React\User\Auth\AuthenticationController;

//health-check
Route::get("/check", function () {
    return "Project running!";
});

//Guest user routes
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

    //cms-routes-get pages dynamic data
    Route::get('/home-page-data', [HomeController::class,'index']);     //get home-page data
    Route::get('/event-page-data', [EventController::class,'index']);    //get event-page data
    Route::get('/feature-page-data', [FeatureController::class,'index']);    //get features-page data
    Route::get('/newsletter-page-data', [NewsletterController::class,'index']);    //get features-page data
});


// Authenticatd user routes
Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::put('/update-role', [AuthenticationController::class, 'updateRole']);

    // Profile update
    Route::get('/profile', [UserProfileController::class, 'profile']);
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']);
    Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']);
    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']);


    //Post routes
    Route::group(['prefix' => 'post'], function () {
        Route::post('/store', [PostController::class, 'store']);    // Create new post
        Route::get('/index', [PostController::class, 'index']);       // Fetch all posts of auth user
        Route::get('/all', [PostController::class, 'getAllPosts']);       // Fetch all posts of other users
        Route::get('/show/{id}', [PostController::class, 'show']);       // Single post view
        Route::delete('/delete/{id}', [PostController::class, 'destroy']); // Delete post
        Route::get('/tag/{tag}', [PostController::class, 'postsByTag']); // Get posts by hashtag
        Route::post('/update/{id}', [PostController::class, 'update']); // Update post

        //post list/unlike
        Route::group(['prefix' => 'like'], function () {
            Route::post('/{post}', [PostLikeController::class, 'toggleLike']);        // Like/Unlike a post
            Route::get('/index/{postId}', [PostLikeController::class, 'index']);           // Get all likes
        });

        // Comment routes
        Route::group(['prefix' => 'comment'], function () {
            Route::post('/store/{postId}', [PostCommentController::class, 'store']);         // Add comment
            Route::get('/list/{postId}', [PostCommentController::class, 'index']);         // Get all comments
            Route::put('/update/{id}', [PostCommentController::class, 'update']);             // Edit comment
            Route::delete('/delete/{id}', [PostCommentController::class, 'destroy']);  // Delete comment
        });

        // Comment reply routes
        Route::group(['prefix' => 'comment-reply'], function () {
            Route::post('/store', [PostCommentReplyController::class, 'store']);             // Create reply
            Route::put('/update/{id}', [PostCommentReplyController::class, 'update']);       // Update reply
            Route::delete('/delete/{id}', [PostCommentReplyController::class, 'destroy']);   // Delete reply
            Route::get('/list/{commentId}', [PostCommentReplyController::class, 'index']);   // List replies of a comment
        });

        // Post share routes
        Route::group(['prefix' => 'post-share'],function () {
            Route::post('/store/{postId}', [PostShareController::class, 'store']); // Create share
            Route::get('/index/{postId}', [PostShareController::class, 'index']); // get share with user and share count
            Route::put('/update/{id}', [PostShareController::class, 'update']); // share update
            Route::delete('delete/{id}', [PostShareController::class, 'destroy']); // share delete
        });
    });
});
