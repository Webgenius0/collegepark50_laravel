<?php

namespace App\Http\Controllers\Api\React\User\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\RegisterOtpMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Cache;

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

            $otp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(5);

            $email = $validatedData['email'];
            $f_name = $validatedData['f_name'];
            $l_name = $validatedData['l_name'];

            // Add OTP + expiry to the cached data
            $cacheData = array_merge($validatedData, [
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
            ]);

            // Store in cache
            Cache::put("register_otp_{$email}", $otp, 300); // 5 minutes
            Cache::put("register_data_{$email}", $cacheData, 300); // 5 minutes

            // Send mail
            // $fullName = $f_name . ' ' . $l_name;
            // Mail::to($email)->send(new RegisterOtpMail($otp, $fullName));

            return $this->success(
                [
                    'message' => 'OTP has been sent to your email. Please verify to complete registration.',
                    'f_name' => $f_name,
                    'l_name' => $l_name,
                    'email' => $email,
                    'otp' => $otp,
                ],
                'OTP Sent successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], 'Something went wrong: ' . $e->getMessage(), 500);
        }
    }


    /*
    ** Resend otp for registration
    */
    public function resendRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Check if cached registration data exists
        $cachedData = Cache::get("register_data_{$email}");

        if (!$cachedData) {
            return $this->error([], 'No registration data found. Please register again.', 404);
        }

        try {
            $otp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(5);

            // Update OTP in cached registration data
            $cachedData['otp'] = $otp;
            $cachedData['otp_expires_at'] = $otpExpiresAt;

            // Save updated cache
            Cache::put("register_otp_{$email}", $otp, 300);
            Cache::put("register_data_{$email}", $cachedData, 300);

            // Send mail
            // $fullName = $cachedData['f_name'] . ' ' . $cachedData['l_name'];
            // Mail::to($email)->send(new RegisterOtpMail($otp, $fullName));

            return $this->success(
                [
                    'message' => 'A new OTP has been sent to your email address.',
                    'email' => $email,
                    'otp' => $otp,
                ],
                'OTP resent successfully.',
                200
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());
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
            $email = $validatedData['email'];
            $otp = $validatedData['otp'];

            $cachedOtp = Cache::get("register_otp_{$email}");
            $cachedData = Cache::get("register_data_{$email}");

            if (!$cachedOtp || !$cachedData) {
                return $this->error([], 'OTP has expired or registration data not found.', 410);
            }

            if ($otp != $cachedOtp) {
                return $this->error([], 'Your OTP is invalid.', 403);
            }

            if (Carbon::now()->gt(Carbon::parse($cachedData['otp_expires_at']))) {
                return $this->error([], 'OTP has expired.', 410);
            }

            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                return $this->error([], 'Email already registered.', 409);
            }

            // Save user to database
            $user = User::create([
                'f_name' => $cachedData['f_name'],
                'l_name' => $cachedData['l_name'],
                'email' => $cachedData['email'],
                'password' => Hash::make($cachedData['password']),
                'is_otp_verified' => true,
                'email_verified_at' => Carbon::now(),
            ]);

            $token = auth('api')->login($user);

            // Clear cache after successful registration
            Cache::forget("register_otp_{$email}");
            Cache::forget("register_data_{$email}");

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
