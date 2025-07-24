<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\React\CMS\HomeController;
use App\Http\Controllers\Api\React\CMS\EventController;
use App\Http\Controllers\Api\React\Post\PostController;
use App\Http\Controllers\Api\React\Venue\VenueController;
use App\Http\Controllers\Api\React\Post\PostLikeController;
use App\Http\Controllers\Api\React\User\FollowerController;
use App\Http\Controllers\Web\Backend\CMS\FeatureController;
use App\Http\Controllers\Api\React\CMS\NewsletterController;
use App\Http\Controllers\Api\React\Event\EventLikeController;
use App\Http\Controllers\Api\React\Post\PostCommentController;
use App\Http\Controllers\Api\React\Event\EventManageController;
use App\Http\Controllers\Api\React\Venue\VenueReviewController;
use App\Http\Controllers\Api\React\Event\EventCommentController;
use App\Http\Controllers\Api\React\Event\EventPageController;
use App\Http\Controllers\Api\React\Notification\NotificationController;
use App\Http\Controllers\Api\React\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\React\User\Auth\UserProfileController;
use App\Http\Controllers\Api\React\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\React\User\Auth\AuthenticationController;
use App\Http\Controllers\Api\React\Venue\VenuePageController;

//health-check
Route::get("/check", function () {
    return "Project is running!";
});

//Guest user routes
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

    //cms-routes-get pages dynamic data
    Route::get('/home-page-data', [HomeController::class, 'index']);     //get home-page data
    Route::get('/event-page-data', [EventController::class, 'index']);    //get event-page data
    Route::get('/feature-page-data', [FeatureController::class, 'index']);    //get features-page data
    Route::get('/newsletter-page-data', [NewsletterController::class, 'index']);    //get features-page data
});



Route::group(['middleware' => 'auth:api'], function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::post('/update-role', [AuthenticationController::class, 'updateRole']);

    //Profile
    Route::get('/profile', [UserProfileController::class, 'profile']);
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::post('/update-location', [UserProfileController::class, 'updateLocation']);
    Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']);
    Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']);

    //user followers routes
    Route::post('/follow/{id}', [FollowerController::class, 'toggleFollow']);              // Follow or unfollow a user by ID (toggle)
    Route::get('/user/{id}/followers', [FollowerController::class, 'getFollowers']);       // Get all followers of a user by user ID
    Route::get('/user/{id}/followings', [FollowerController::class, 'getFollowings']);     // Get all users that a user is following by user ID


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

        // Comment-reply routes
        Route::group(['prefix' => 'comment'], function () {
            Route::post('/store/{postId}', [PostCommentController::class, 'store']);         // Add comment
            Route::get('/list/{postId}', [PostCommentController::class, 'index']);         // Get all comments
            Route::post('/update/{id}', [PostCommentController::class, 'update']);             // Edit comment
            Route::delete('/delete/{id}', [PostCommentController::class, 'destroy']);  // Delete comment
        });
    });

    //Venue routes
    Route::prefix('venue')->group(function () {
        //venue page route according to prototype
        Route::get('/', [VenuePageController::class, 'allVenue']);  // List all events
        Route::get('/details/{id}', [VenuePageController::class, 'venueDetails']);  // get a single venue with detials by id

        //venue manage
        Route::post('/store', [VenueController::class, 'store']);           // Store a new venue
        Route::get('/edit/{id}', [VenueController::class, 'edit']);    // Get a venue for editing
        Route::post('/update/{id}', [VenueController::class, 'update']);       // Update a venue
        Route::patch('/status/{id}', [VenueController::class, 'status']); // Update status only
        Route::delete('/delete/{id}', [VenueController::class, 'destroy']);   // Delete a venue

        //venue review/feedback
        Route::prefix('reviews')->group(function () {
            Route::get('/{venue_id}', [VenueReviewController::class, 'index']);     // Get all reviews for a venue
            Route::post('/{venue_id}', [VenueReviewController::class, 'store']);    // Add or update review for a venue
            Route::delete('/delete/{id}', [VenueReviewController::class, 'destroy']);  // Delete a review
        });
    });

    // Event routes
    Route::prefix('event')->group(function () {
        //event page route according to prototype
        Route::get('/', [EventPageController::class, 'allEvents']);  // List all events
        Route::get('/upcoming', [EventPageController::class, 'upcomingEvents']);  // Get upcoming events only
        Route::get('/past', [EventPageController::class, 'pastEvents']); // Get pasts events
        Route::get('/my', [EventPageController::class, 'myEvents']); // Get authorized user events
        Route::get('/gallery', [EventPageController::class, 'eventGallery']); //Get event's images

        //event manage
        Route::post('/store', [EventManageController::class, 'store']);       // Create a new event
        Route::get('/show/{id}', [EventManageController::class, 'show']);     // Get a specific event
        Route::post('/update/{id}', [EventManageController::class, 'update']); // Update an event
        Route::delete('/delete/{id}', [EventManageController::class, 'destroy']); // Delete an event
        Route::post('/change-status/{id}', [EventManageController::class, 'changeStatus']); // Update event status


        //event list/unlike
        Route::group(['prefix' => 'like'], function () {
            Route::post('/{event}', [EventLikeController::class, 'toggleLike']);        // Like/Unlike an event
            Route::get('/index/{eventId}', [EventLikeController::class, 'index']);           // Get all likes
        });

        // Comment-reply routes
        Route::group(['prefix' => 'comment'], function () {
            Route::post('/store/{eventId}', [EventCommentController::class, 'store']);         // Add comment
            Route::get('/list/{eventId}', [EventCommentController::class, 'index']);         // Get all comments
            Route::post('/update/{id}', [EventCommentController::class, 'update']);             // Edit comment
            Route::delete('/delete/{id}', [EventCommentController::class, 'destroy']);  // Delete comment
        });


    });


    Route::get('/my-notifications', [NotificationController::class, 'allNotifications']);
    Route::post('/read-notification/{id}', [NotificationController::class, 'readNotification']);
});
