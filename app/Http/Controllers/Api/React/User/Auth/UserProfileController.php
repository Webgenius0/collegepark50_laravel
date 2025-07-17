<?php

namespace App\Http\Controllers\Api\React\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
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
            $userData = [
                'id' => $user->id,
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role,
            ];
            return $this->success($userData, 'User Profile Retrieved successfully', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'f_name' => ['nullable', 'string', 'max:50'],
                'l_name' => ['nullable', 'string', 'max:50'],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
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
            $userData = [
                'id' => $user->id,
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'email' => $user->email,
                'avatar' => $user->avatar ? url($user->avatar) : null,
                'role' => $user->role,
                'provider' => $user->provider,
                'provider_id' => $user->provider_id,
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
