<?php
namespace App\Http\Controllers\Api\User\Auth;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'          => ['required', 'string', 'max:255'],
                'email'         => ['required', 'string', 'email', 'unique:users', 'max:255'],
                'password'      => ['required', 'string', 'min:8'],
                'phone_number'  => ['nullable'],
                'date_of_birth' => ['nullable'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 404);
            }

            $validatedData = $validator->validated();
            $user          = User::where('email', $validatedData['email'])->first();
            $otp           = rand(1000, 9999);
            $otpExpiresAt  = Carbon::now()->addMinutes(5);

            $user = User::create([

                'name'            => $validatedData['name'],

                'email'           => $validatedData['email'],
                'password'        => Hash::make($validatedData['password']),

                'phone_number'    => $validatedData['phone_number'],
                'date_of_birth'   => $validatedData['date_of_birth'],
                'age'             => Helper::calculateAge($validatedData['date_of_birth']),

                'otp'             => $otp,
                'otp_expires_at'  => $otpExpiresAt,
                'is_otp_verified' => false,

            ]);

            // Mail::to($user->email)->send(new RegisterOtpMail($otp, $user->name));

            return $this->success([
                'message' => 'OTP has been sent to your email. Please verify to complete registration.',
                'email'   => $user->email,
                'opt'     => $user->otp,
            ], 'OTP Sent', 201);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function RegistrationVerifyOtp(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'otp'   => ['required', 'digits:4'],
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 400);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error([], 'User not found', 200);
        }

        if ($user->otp !== $request->otp) {
            return $this->error([], 'Your OTP is Invalid.', 403);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return $this->error([], 'OTP has expired');
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'is_otp_verified'   => true,
            'otp'               => null,
            'otp_expires_at'    => null,
        ]);

        $token = auth('api')->login($user);

        $userData = [

            'id'            => $user['id'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'phone_number'  => $user['phone_number'],
            'date_of_birth' => $user['date_of_birth'],
            'role'          => $user['role'],

            'created_at'    => Carbon::parse($user['created_at'])->format('Y-m-d H:i:s'),

            'token'         => $token,
        ];

        return $this->success($userData, 'User Registration successful.', 200);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 200);
            }

            $data = $validator->validated();

            $user = User::where('email', $data['email'])->first();

            if (! $user) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            // Check if OTP is verified
            if (! $user->is_otp_verified) {
                return $this->error([], 'Please verify your email with the OTP before logging in.', 401);
            }

            if (! $token = auth('api')->attempt($data)) {
                return $this->error([], 'Invalid email or password.', 401);
            }

            // Load related employee and company profiles (if applicable)
            $employee = $user->employee;
            $company  = $user->company;

            $userData = [
                'id'                        => $user->id,
                'name'                      => $user->name,
                'email'                     => $user->email,
                'role'                      => $user->role ?? null,
                'phone_number'              => $user->phone_number ?? null,
                'date_of_birth'             => $user->date_of_birth ?? null,
                'token'                     => $token,

                // Employee presence flags
                'employee_profile'          => $employee ? true : false,
                'employee_location'         => $employee && $employee->location ? true : false,
                'employee_specialize'       => $employee && ! $employee->specializations->isEmpty() ? true : false,
                'employee_job_categories'   => $employee && ! $employee->employee_job_categories->isEmpty() ? true : false,

                // Company presence flags
                'company_image'             => $company && $company->image_url ? true : false,
                'company_specialize'        => $company && $company->company_specializes ? true : false,

                'company_profile_complete'  => ! empty($company->image_url) && ! empty($company->name) && ! empty($company->display_name) && ! empty($company->bio) && ! empty($company->location),
                'employee_profile_complete' => ! empty($employee->location) && ! empty($employee->image_url) && ! empty($employee->bio),

            ];

            return $this->success($userData, 'Successfully Logged In', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function updateRole(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (! $user) {
                return $this->error([], 'User not found .', 400);
            }

            if ($user->role !== null) {
                return $this->error([], 'You have already updated your role. Role cannot be changed again.', 400);
            }

            // update user role
            $user->update(['role' => $request->role]);

            // Prepare response data
            $userData = [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone_number'  => $user->phone_number,
                'date_of_birth' => $user->date_of_birth,
                'role'          => $user->role,

                'created_at'    => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at'    => $user->updated_at->format('Y-m-d H:i:s'),
            ];

            return $this->success($userData, 'User role updated successfully.', 200);
        } catch (Exception $e) {
            Log::error('Role update error: ' . $e->getMessage());
            return $this->error([], 'An error occurred while updating the role.', 500);
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->success([], 'Successfully logged out.', 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
