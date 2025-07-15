<?php
namespace App\Http\Controllers\Api\Website;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\CompanyJob;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserManageController extends Controller
{
    use ApiResponse;

    public function user_info(Request $request)
    {

        $user = User::with(['employee.experiences', 'employee.certifications', 'employee.qualifications', 'employee.specializations.specialize', 'employee.employee_job_categories.job_category', 'company', 'company.company_specializes.specialize', 'company.company_projects'])->where('id', auth('api')->user()->id)->first();

        if (! $user) {
            return $this->error([], 'User not found.', 404);
        }

        $employee = $user->employee;
        $company  = $user->company;

        $data = [

            'id'                        => $user->id,
            'name'                      => $user->name ?? null,
            'email'                     => $user->email ?? null,
            'role'                      => $user->role,
            'status'                    => $user->status,
            'age'                       => $user->age,
            'gender'                    => $user->gender,
            'phone_number'              => $user->phone_number,
            'date_of_birth'             => $user->date_of_birth,
            'avatar'                    => $user->avatar,

            'employee_profile_complete' => ! empty($employee->location) && ! empty($employee->image_url) && ! empty($employee->bio),
            'employee'                  => $user->employee ?? null,

            // 'employee_location'       =>  ,
            // 'employee_specialize'     => $employee && ! $employee->specializations->isEmpty() ? true : false,
            // 'employee_job_categories' => $employee && ! $employee->employee_job_categories->isEmpty() ? true : false,

            // Company presence flags
            'company_profile_complete'  => ! empty($company->image_url) && ! empty($company->name) && ! empty($company->display_name) && ! empty($company->bio) && ! empty($company->location),

            'company'                   => $user->company ?? null,

        ];

        return $this->success($data, 'User Information retrieved successfully.');
    }

    public function user_avatar_update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors(), 'Validation Failed', 422);
            }

            $user = auth('api')->user();

            if (! $user) {
                return $this->error([], 'User not authenticated.', 401);
            }

            // Handle avatar
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar) {
                    Helper::deleteImage($user->avatar);
                }

                // Upload new avatar
                $image  = $request->file('avatar');
                $avatar = Helper::uploadImage($image, 'profile');

                // Save avatar based on user role
                if ($user->role === "employee") {
                    $user->avatar = $avatar;
                } else {
                    $company = $user->company;
                    if ($company) {
                        $company->image_url = $avatar;
                        $company->save();
                    }
                }
            }

            $user->save();

            $userData = [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar ?? $user->company->image_url,
            ];

            return $this->success($userData, 'Avatar updated successfully.', 200);

        } catch (\Exception $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error([], 'An unexpected error occurred. Please try again.', 500);
        }
    }

    // job list for website
    public function featuredJobList(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $jobs = CompanyJob::with('company')->latest();

        // Apply filters before pagination
        if ($request->has('job_category_ids')) {
            $jobs->whereIn('job_category_id', $request->job_category_ids);
        }

        if ($request->filled('location')) {
            $jobs->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('certification')) {
            $jobs->where('certification', 'LIKE', '%' . $request->certification . '%');
        }

        if ($request->has('min_salary') && $request->has('max_salary')) {
            $salaryMin = (int) $request->min_salary;
            $salaryMax = (int) $request->max_salary;
            $jobs->whereBetween(DB::raw('CAST(salary AS SIGNED)'), [$salaryMin, $salaryMax]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $jobs->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%')
                    ->orWhere('job_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('salary', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('company', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('display_name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        // Paginate after filtering
        $paginatedJobs = $jobs->paginate($perPage);

        // Structure the response
        $response = [
            'success' => true,
            'message' => 'Featured Jobs retrieved successfully',
            'data'    => [

                'featured_jobs' => $paginatedJobs->items(),

                'pagination'    => [
                    'current_page' => $paginatedJobs->currentPage(),
                    'last_page'    => $paginatedJobs->lastPage(),
                    'per_page'     => $paginatedJobs->perPage(),
                    'total'        => $paginatedJobs->total(),
                ],
            ],
            'code'    => 200,
        ];

        return response()->json($response, 200);
    }

    public function recommadedJobList(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Correct key for pagination

        $jobs = CompanyJob::with('company')->latest();

        // Apply filters before pagination
        if ($request->has('job_category_ids')) {
            $jobs->whereIn('job_category_id', $request->job_category_ids);
        }

        if ($request->filled('location')) {
            $jobs->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->filled('certification')) {
            $jobs->where('certification', 'LIKE', '%' . $request->certification . '%');
        }

        if ($request->has('min_salary') && $request->has('max_salary')) {
            $salaryMin = (int) $request->min_salary;
            $salaryMax = (int) $request->max_salary;
            $jobs->whereBetween(DB::raw('CAST(salary AS SIGNED)'), [$salaryMin, $salaryMax]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $jobs->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('location', 'LIKE', '%' . $search . '%')
                    ->orWhere('job_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('salary', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('company', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('display_name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        $recommendedJobs = $jobs->paginate($perPage);

        if ($recommendedJobs->isEmpty()) {
            return $this->success(null, 'No jobs found');
        }

        // Structure response similar to featuredJobList
        $data = [

            'recommended_jobs' => $recommendedJobs->items(),

            'pagination'       => [
                'current_page' => $recommendedJobs->currentPage(),
                'last_page'    => $recommendedJobs->lastPage(),
                'per_page'     => $recommendedJobs->perPage(),
                'total'        => $recommendedJobs->total(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Recommended Jobs retrieved successfully',
            'data'    => $data,
            'code'    => 200,
        ], 200);
    }

}
