<?php

namespace App\Http\Controllers\Api\React\User\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use ApiResponse;


    public function profile()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not found.', 200);
            }

            return $this->success(new UserResource($user), 'User Profile Retrieved successfully', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'f_name'       => ['nullable', 'string', 'max:50'],
                'l_name'       => ['nullable', 'string', 'max:50'],
                'avatar'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
                'profession'   => ['nullable', 'string', 'max:100'],
                'gender'       => ['nullable', 'string', 'in:male,female,Other'],
                'country'      => ['nullable', 'string', 'max:255'],
                'age'          => ['nullable', 'integer', 'min:0'],
                'address'      => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            $data = $validator->validated();

            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }
                $avatarPath = Helper::uploadImage($request->file('avatar'), 'profile');
                $data['avatar'] = $avatarPath;
            }

            $user->update($data);


            return $this->success(new UserResource($user), 'Profile updated successfully.', 200);

        } catch (Exception $e) {

            Log::error('Profile Update Error: ' . $e->getMessage());
            return $this->error([], 'Failed to update profile.', 500);
        }
    }


    public function updateLocation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address'       => ['nullable', 'string', 'max:50'],
                'city'       => ['nullable', 'string', 'max:50'],
                'state'       => ['nullable', 'string', 'max:255'],
                'zip_code'   => ['nullable', 'string', 'max:100'],
                'latitude'       => ['nullable', 'string', 'max:255'],
                'longitude'      => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            $data = $validator->validated();

            $user->update($data);

            $userData = [
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip_code' => $user->zip_code,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
            ];


            return $this->success($userData, 'Profile updated successfully.', 200);

        } catch (Exception $e) {

            Log::error('Profile Update Error: ' . $e->getMessage());
            return $this->error([], 'Failed to update profile.', 500);
        }
    }


    public function updateAvatar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();

            if ($user->avatar) {
                Helper::deleteImage($user->avatar);
            }

            $avatarPath = Helper::uploadImage($request->file('avatar'), 'profile');

            $user->update(['avatar' => $avatarPath]);

            return $this->success(['avatar' => url($avatarPath)], 'Avatar updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Avatar Update Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string', 'min:8'],
                'password'  => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 200);
            }

            $user = auth('api')->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->error([], 'current password is incorrect.', 200);
            }

            $user->update(['password' => Hash::make($request->password)]);

            return $this->success(['Password updated successfully'], 'Password updated successfully.', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function deleteProfile()
    {
        try {
            $user = auth('api')->user();

            if ($user->avatar) {
                Helper::deleteImage($user->avatar);
            }

            $user->delete();

            return $this->success([], 'Profile deleted successfully.', 200);
        } catch (Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
