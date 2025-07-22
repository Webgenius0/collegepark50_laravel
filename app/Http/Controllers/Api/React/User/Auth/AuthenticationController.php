<?php

namespace App\Http\Controllers\Api\React\User\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\RegisterOtpMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\Auth\OtpVerifyRequest;
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

            $otp = rand(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(5);

            $user = User::create([
                'f_name' => $validatedData['f_name'],
                'l_name' => $validatedData['l_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'is_otp_verified' => false,
            ]);

            $fullName = $user->f_name . ' ' . $user->l_name;

            Mail::to($user->email)->send(new RegisterOtpMail($otp, $fullName));

            return $this->success(
                [
                    'message' => 'OTP has been sent to your email. Please verify to complete registration.',
                    'f_name' => $user->f_name,
                    'l_name' => $user->l_name,
                    'email' => $user->email,
                    'otp' => $user->otp,
                ],
                'OTP Sent successfully.',
                201,
            );

        } catch (Exception $e) {

            Log::info($e->getMessage());
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /*
    ** Verify Register Otp
    */
    public function RegistrationVerifyOtp(OtpVerifyRequest $request)
    {
        try {

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

            $userData = [
                'id' => $user->id,
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'email' => $user->email,
                'role' => $user->role,
                'is_otp_verified' => $user->is_otp_verified,
                'token' => $token,
            ];

            return $this->success($userData, 'Otp verified successfully. You are now registered.', 200);

        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
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

            if (!$user->is_otp_verified) {
                return $this->error([], 'Please verify your email with the OTP before logging in.', 401);
            }


            if (!($token = auth('api')->attempt($validatedData))) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            $userData = [
                'id' => $user->id,
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'email' => $user->email,
                'role' => $user->role,
                'token' => $token,
            ];

            return $this->success($userData, 'Successfully logged in!.', 200);

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

            // dd($user);

            if (!$user) {
                return $this->error([], 'User not found.', 404);
            }

            $validator = Validator::make($request->all(), [
                'role' => 'required|in:user,dj,promoter,artist,venue,admin',
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            if (!empty($user->role) && $user->role !== 'user') {
                return $this->error([], 'You have already updated your role. It cannot be changed again.', 400);
            }


            $user->update(['role' => $request->role]);

            $userData = [
                'id' => $user->id,
                'role' => $user->role,
            ];

            return $this->success($userData, 'User role updated successfully.', 200);

        } catch (Exception $e) {

            Log::info($e->getMessage());
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

            Log::info($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
