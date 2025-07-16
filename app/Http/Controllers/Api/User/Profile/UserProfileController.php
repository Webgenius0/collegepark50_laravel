<?php

namespace App\Http\Controllers\Api\User\Profile;

use Exception;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Requests\Profile\LocationUpdateRequest;

class UserProfileController extends Controller
{
    use ApiResponse;

    /*
    ** get user profile
    */
    public function show()
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized', 401);
            }

            return $this->success([
                'user' => new UserResource($user)
            ], 'Profile retrieved');
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch user profile', 500);
        }
    }

    /*
    ** update user profile
    */
    public function update(ProfileUpdateRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Handle avater upload/update
            if ($request->hasFile('avater')) {
                if ($user->avater) {
                    Helper::deleteImage($user->avater);
                }

                $image = $request->file('avater');
                $validatedData['avater'] = Helper::uploadImage($image, 'profile');
            }

            // Filter out null values to prevent overwriting
            $updateData = array_filter(
                Arr::only($validatedData, [
                    'first_name',
                    'last_name',
                    'profession',
                    'gender',
                    'country',
                    'age',
                    'address',
                    'avater'
                ]),
                fn($value) => !is_null($value)
            );

            $user->update($updateData);

            return $this->success(new UserResource($user), 'Profile updated successfully.', 200);
        } catch (Exception $e) {
            return $this->error([], 'Failed to update profile. Please try again.', 500);
        }
    }

    /*
    ** update notification status
    */
    public function status(Request $request)
    {
        try {

            $request->validate([
                'get_notification' => ['required', 'boolean']
            ]);

            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            $user->get_notification = $request->input('get_notification');
            $user->save();

            return $this->success(
                ['get_notification' => $user->get_notification],
                'Notification status updated successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error(
                [],
                'Failed to update notification status. Please try again.',
                500
            );
        }
    }

    /*
    ** update location preference
    */
    public function location(LocationUpdateRequest $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            $data = array_filter($request->only([
                'address',
                'city',
                'state',
                'latitude',
                'longitude'
            ]), fn($value) => !is_null($value));

            $user->update($data);

            return $this->success(
                new UserResource($user),
                'Location updated successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to update location.', 500);
        }
    }
}
