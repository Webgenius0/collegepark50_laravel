<?php

namespace App\Http\Controllers\Api\User\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use ApiResponse;

    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'string', 'max:255'],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
                'date_of_birth' => ['nullable'],
                'location' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation Failed', 422);
            }

            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Handle avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }
                $image = $request->file('avatar');
                $user->avatar = Helper::uploadImage($image, 'profile');
            }

            // Update user fields
            $user->name = $request->input('name', $user->name);
            $user->date_of_birth = $request->input('date_of_birth', $user->date_of_birth);
            $user->save();

            // Update company if exists
            $company = Company::where('user_id', $user->id)->first();
            if ($company && $request->filled('location')) {
                $company->location = $request->location;
                $company->save();
            }

            // Prepare updated user data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth,
                'location' => $company->location ?? null,
            ];

            return $this->success($userData, 'Profile updated successfully.', 200);
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }


    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error([], 'Current password is incorrect.', 403);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return $this->success(true, 'Password updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Password update failed: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            ]);

            $user = Auth::user();

            if ($user->avatar) {
                Helper::deleteImage($user->avatar);
            }

            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $imagePath = Helper::uploadImage($image, 'profile');
                $user->avatar = $imagePath;
            }

            $user->save();

            $updatedUser = User::select('id', 'avatar')->find(auth('api')->id());

            return $this->success($updatedUser, 'Avatar updated successfully.', 200);
        } catch (Exception $e) {

            Log::error('Avatar update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }

    public function me()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }
            // company

            $company = Company::where('user_id', $user->id)->first();

            if (!$company) {
                return $this->error([], 'Company not found.', 404);
            }

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'date_of_birth' => $user->date_of_birth ?? null,
                'location' => $company->location ?? null,
            ];

            return $this->success($userData, 'User Profile Retrived successfull', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function deleteProfile()
    {
        $user = User::find(auth('api')->id());

        if (!$user) {
            return $this->success([], 'User Profile not found', 200);
        }

        if ($user->avatar) {
            Helper::deleteImage($user->avatar);
        }

        $user->delete();
        return $this->success(true, 'Profile deleted successfully', 200);
    }









    public function employee_profile()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }
            // company


            $employee = $user->employee;


            $company = $user->company;



            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'date_of_birth' => $user->date_of_birth ?? null,
                'location' => $employee && $employee->location ? $employee->location : null,
                'bio' => $employee && $employee->bio ? $employee->bio : null,



                 // Employee presence flags
                'employee_profile' => $employee ? true : false,
                'employee_location' =>  $employee && $employee->location ? true : false,
                'employee_specialize' => $employee && !$employee->specializations->isEmpty() ? true : false,
                'employee_job_categories' => $employee && !$employee->employee_job_categories->isEmpty() ? true : false,

                // Company presence flags
                'company_image' =>  $company && $company->image_url ? true : false,
                'company_specialize' =>     $company && $company->company_specializes ? true : false,




            ];

            return $this->success($userData, 'Employee Profile Retrived successfull', 200);


        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }


    // employee profile update
    public function employee_profile_update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'string', 'max:255'],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
                'date_of_birth' => ['nullable'],
                'location' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation Failed', 422);
            }

            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Handle avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }
                $image = $request->file('avatar');
                $user->avatar = Helper::uploadImage($image, 'profile');
            }
            $user->name = $request->input('name', $user->name);
            $user->date_of_birth = $request->input('date_of_birth', $user->date_of_birth);
            $user->save();


            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee && $request->filled('location')) {
                $employee->location = $request->location;
                $employee->save();
            }

            // Prepare updated user data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth,
                'location' => $employee->location ?? null,
            ];

            return $this->success($userData, 'Employee Profile updated successfully.', 200);
        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }











}
