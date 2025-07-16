<?php

namespace App\Http\Controllers\Api\User\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\RegisterOtpMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpVerifyRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\UserRegisterRequest;

class AuthenticationController extends Controller
{
    use ApiResponse;

    /*
    ** User registration
    */
    public function register(UserRegisterRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $otp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(5);

            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'is_otp_verified' => false,
            ]);

            // full name
            $fullName = $user->first_name . ' ' . $user->last_name;

            Mail::to($user->email)->send(new RegisterOtpMail($otp, $fullName));

            return $this->success(
                [
                    'message' => 'OTP has been sent to your email. Please verify to complete registration.',
                    'email' => $user->email,
                    'otp' => $user->otp,
                ],
                'OTP Sent',
                201,
            );
        } catch (Exception $e) {
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /*
    ** Verify Register Otp
    */
    public function RegistrationVerifyOtp(OtpVerifyRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        if ($user->otp !== $validatedData['otp']) {
            return $this->error([], 'Your OTP is invalid.', 403);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return $this->error([], 'OTP has expired', 410);
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'is_otp_verified'   => true,
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);

        $token = auth('api')->login($user);

        return $this->success([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'User registration successful.');
    }


    /*
    ** User login
    */
    public function login(LoginRequest $request)
    {
        try {

            $validatedData = $request->validated();

            $user = User::where('email', $validatedData['email'])->first();

            if (!$user) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            // Check if OTP is verified
            if (!$user->is_otp_verified) {
                return $this->error([], 'Please verify your email with the OTP before logging in.', 401);
            }

            if (!($token = auth('api')->attempt($validatedData))) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'Successfully logged in!.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    /*
    ** Update user role
    */
    public function updateRole(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            // validate role
            $request->validate([
                'role' => ['required', 'in:user,dj,promoter,artist,venue,other'],
            ]);

            if (!empty($user->role) && $user->role !== 'user') {
                return $this->error([], 'You have already updated your role. It cannot be changed again.', 400);
            }

            // update user role
            $user->update(['role' => $request->role]);

            return $this->success([
                'user'       => new UserResource($user),
            ], 'User role updated successfully.');
        } catch (Exception $e) {
            return $this->error([], 'An error occurred while updating the role.', 500);
        }
    }


    /*
    ** User logout
    */
    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->success([], 'Successfully logged out.', 200);
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
