<?php

use App\Http\Controllers\Api\Company\JobManageController;
use App\Http\Controllers\Api\Company\ManageCompanyController;
use App\Http\Controllers\Api\Employee\ChatSystemController;
use App\Http\Controllers\Api\Employee\EmployeeController;
use App\Http\Controllers\Api\Employee\JobApplicationController;
use App\Http\Controllers\Api\Employee\JobBoardController;
use App\Http\Controllers\Api\Employee\ReportController;
use App\Http\Controllers\Api\User\Auth\AuthenticationController;
use App\Http\Controllers\Api\User\Auth\ResetPasswordController;
use App\Http\Controllers\Api\User\Auth\SocialLoginController;
use App\Http\Controllers\Api\User\Auth\UserProfileController;
use App\Http\Controllers\Api\Website\UserManageController;
use App\Http\Controllers\Web\Backend\Settings\DynamicPageController;
use App\Http\Controllers\Web\Backend\SpecializeController;
use App\Http\Controllers\Web\Backend\SplashController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('splash', [SplashController::class, 'Splash']);

Route::get('privacy-policy', [DynamicPageController::class, 'privacyPolicy']);
Route::get('term-conditions', [DynamicPageController::class, 'agreement']);

Route::get('/employee/specialize', [SpecializeController::class, 'employee_list']);
Route::get('/company/specialize', [SpecializeController::class, 'company_list']);
Route::get('/job/categories', [SpecializeController::class, 'job_categories']);

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
    Route::post('forgot-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);

    // Social Login
    Route::post('social/signin/{provider}', [SocialLoginController::class, 'socialSignin']);
});

// logout
Route::post('/logout', [AuthenticationController::class, 'logout']);
Route::delete('/delete-profile', [UserProfileController::class, 'deleteProfile']);

// search company
Route::get('/company/search', [EmployeeController::class, 'searchCompany']);

//  repoert
Route::post('/report', [ReportController::class, 'report']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Prefix: auth)
|--------------------------------------------------------------------------
*/

Route::post('/update/role', [AuthenticationController::class, 'updateRole']);

Route::middleware(['auth:employee', 'role:employee'])->prefix('auth')->group(function () {

    // update role

    // Employee CRUD
    Route::get('/employees', [EmployeeController::class, 'index'])->name('admin.employee.index');
    Route::post('/employees/store', [EmployeeController::class, 'store'])->name('admin.employee.store');

    Route::post('employee/update/experience', [EmployeeController::class, 'addExperience']);
    Route::post('employee/update/certification', [EmployeeController::class, 'addCertification']);
    Route::post('employee/update/qualification', [EmployeeController::class, 'addQualification']);

    // User Profile and avatar
    Route::get('/employee/profile', [UserProfileController::class, 'employee_profile']);
    Route::post('/employee/profile/update', [UserProfileController::class, 'employee_profile_update']);

    Route::post('/update-avatar', [UserProfileController::class, 'updateAvatar']);
    Route::post('/update-password', [UserProfileController::class, 'updatePassword']);
    Route::post('/update-card-info', [UserProfileController::class, 'updateCardInfo']);

    // featured job
    Route::get('/employee/job/featured', [JobBoardController::class, 'featuredJobList']);

    // job details
    Route::get('/employee/job/details/{id}', [JobBoardController::class, 'jobDetails']);

    // recommended job
    Route::get('/employee/job/recommended', [JobBoardController::class, 'recommendedJobList']);

    // job application
    Route::post('/employee/job/apply', [JobApplicationController::class, 'applyJob']);
});

// company routes
Route::middleware(['auth:company', 'role:company'])->prefix('auth')->group(function () {

    // create company
    Route::get('/company/profile/show', [ManageCompanyController::class, 'getCompany']);
    Route::post('/company/create', [ManageCompanyController::class, 'store']);

    // add new project
    Route::post('/company/project/update', [ManageCompanyController::class, 'storeProject']);

    // add new specialize
    Route::post('/company/specialize/update', [ManageCompanyController::class, 'updateSpecialize']);

    Route::get('/company/profile/edit', [UserProfileController::class, 'me']);
    Route::post('/company/profile/update', [UserProfileController::class, 'updateProfile']);

    // job management
    Route::get('/company/job/list', [JobManageController::class, 'getAllJobs']);
    Route::get('/company/job/show/{id}', [JobManageController::class, 'jobDetails']);
    Route::post('/company/job/store', [JobManageController::class, 'store']);
    Route::post('/company/job/update/{id}', [JobManageController::class, 'update']);

    // job applicants
    Route::get('/company/job/applicants/{id}', [JobManageController::class, 'jobApplicants']);
    Route::get('company/job/applicant/employee/{employeeId}', [JobManageController::class, 'jobApplicantDetails']);

    // employee details
    Route::get('company/job/applicant/employee/details/{id}', [JobManageController::class, 'employee_details']);

});

// chat with company route
Route::middleware(['auth:api'])->controller(ChatSystemController::class)->prefix('auth/chat')->group(function () {

    Route::get('/rooms', 'get_rooms');
    Route::get('/user/room/{room_id}', 'get_room_meesage');

    Route::post('/send/message', 'send_message');

});

// website route list

// Route::middleware('auth')->prefix('auth')->group(function () {

Route::get('/website/user/details', [UserManageController::class, 'user_info']);
Route::post('/website/user/avatar/update', [UserManageController::class, 'user_avatar_update']);

// job list

Route::get('/website/featured/jobs/list', [UserManageController::class, 'featuredJobList']);
Route::get('/website/recommanded/jobs/list', [UserManageController::class, 'recommadedJobList']);

// });
