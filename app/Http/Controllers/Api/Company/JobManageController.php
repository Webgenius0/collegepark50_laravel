<?php

namespace App\Http\Controllers\Api\Company;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CompanyJob;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JobManageController extends Controller
{
    use ApiResponse;

    // Get authenticated company
    private function getAuthenticatedCompany()
    {
        $user = auth('api')->user();
        return Company::where('user_id', $user->id)->first();
    }

    // Get all jobs
    public function getAllJobs(Request $request)
    {

        $company = $this->getAuthenticatedCompany();

        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }

        $search = $request->search;
        // dd($search);

        $jobs = CompanyJob::where('company_id', $company->id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    if (is_numeric($search)) {
                        $q->where('salary', '>=', (int)$search);
                    } else {
                        $q->where('title', 'LIKE', '%' . $search . '%')
                            ->orWhere('description', 'LIKE', '%' . $search . '%')
                            ->orWhere('location', 'LIKE', '%' . $search . '%')
                            ->orWhere('job_type', 'LIKE', '%' . $search . '%')
                            ->orWhere('requirement', 'LIKE', '%' . $search . '%')
                            ->orWhere('benefits', 'LIKE', '%' . $search . '%');
                    }
                });
            })
            ->get();




        if ($jobs->isEmpty()) {
            return $this->success([], 'No jobs found for this company.');
        }
        $formattedJobs = $jobs->map(function ($job) {
            return [
                'id' => $job->id,

                'company_id' => $job->company_id,
                'company_name' => $job->company->name,
                'company_logo' => $job->company->image_url ? url($job->company->image_url) : null,
                'company_location' => $job->company->location,

                'title' => $job->title,
                'description' => $job->description,
                'location' => $job->location,
                'salary' => $job->salary,
                'job_type' => $job->job_type,
                'benefits' => $job->benefits,
                'year_of_experience' => $job->year_of_experience,
                'certification' => $job->certification,
                'education' => $job->education,
                'created_at' => Carbon::parse($job->created_at)->format('Y-m-d H:i:s'),
            ];
        });


        $data = [
            'company' => $company,
            'jobs' => $formattedJobs,

        ];




        return $this->success($data, 'Company jobs retrieved successfully.');
    }

    // Get job details
    public function jobDetails($id)
    {
        $company = $this->getAuthenticatedCompany();

        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }

        $job = CompanyJob::where('company_id', $company->id)->find($id);

        if (!$job) {
            return $this->error([], 'Job not found.', 404);
        }

        return $this->success([
            'company' => $company,
            'job' => $job,
        ], 'Job retrieved successfully.');
    }

    // Store new job
    public function store(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'salary' => 'nullable',
            'salary_type' => 'nullable',
            'job_category_id' => 'nullable',
            'benefits' => 'nullable|string',
            'year_of_experience' => 'nullable|string',
            'certification' => 'nullable|string',
            'education' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed.', 422);
        }

        $company = $this->getAuthenticatedCompany();

        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }

        $job = CompanyJob::create([
            'company_id' => $company->id,
            'job_category_id' => $request->job_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'salary' => $request->salary,
            'salary_type' => $request->salary_type,
            
            'benefits' => $request->benefits,
            'year_of_experience' => $request->year_of_experience,
            'certification' => $request->certification,
            'education' => $request->education,
        ]);

        return $this->success($job, 'Job created successfully.', 201);
    }


    // Update job
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'salary' => 'nullable',
            'job_type' => 'nullable|string',
            'benefits' => 'nullable|string',
            'year_of_experience' => 'nullable|string',
            'certification' => 'nullable|string',
            'education' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 200);
        }



        $company = $this->getAuthenticatedCompany();

        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }

        $job = CompanyJob::where('company_id', $company->id)->find($id);
        if (!$job) {
            return $this->error([], 'Job not found.', 404);
        }

        $job->update([
            'title' => $request->title ?? $job->title,
            'description' => $request->description ?? $job->description,
            'location' => $request->location ?? $job->location,
            'salary' => $request->salary ?? $job->salary,
            'job_type' => $request->job_type ?? $job->job_type,
            'benefits' => $request->benefits ?? $job->benefits,
            'year_of_experience' => $request->year_of_experience ?? $job->year_of_experience,
            'certification' => $request->certification ?? $job->certification,
            'education' => $request->education ?? $job->education,
        ]);
        return $this->success($job, 'Job updated successfully.', 200);
    }

    public function jobApplicants(Request $request, $id)
    {

        $company = $this->getAuthenticatedCompany();

        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }

        $job = CompanyJob::where('company_id', $company->id)->find($id);

        if (!$job) {
            return $this->error([], 'Job not found.', 404);
        }


        $years_of_experience = $request->years_of_experience;
        $job_category_ids = $request->job_category_ids;
        $certification = $request->certification;
        $location = $request->location;
        $age = $request->age;
        $search = $request->search;





        $ageRange = explode('-', $age);
        $minAge = isset($ageRange[0]) ? (int)$ageRange[0] : null;
        $maxAge = isset($ageRange[1]) ? (int)$ageRange[1] : null;

        // dd($minAge, $maxAge);

        $yearRange = explode('-', $years_of_experience);
        $minExperience = isset($yearRange[0]) ? (int)$yearRange[0] : null;
        $maxExperience = isset($yearRange[1]) ? (int)$yearRange[1] : null;
        // dd($minExperience, $maxExperience);



        $applicantsQuery = $job->jobApplicants()->with(['employee.experiences', 'employee.user']);

        if ($location || !empty($job_category_ids) || $minAge || $maxAge || $years_of_experience || $certification) {
            $applicantsQuery->whereHas('employee', function ($query) use ($location, $job_category_ids, $minAge, $maxAge, $minExperience, $maxExperience, $certification) {




                if ($location) {
                    $query->where('address', 'LIKE', '%' . $location . '%');
                }


                $min = min($minAge, $maxAge);
                $max = max($minAge, $maxAge);

                if ($minAge && $maxAge) {
                    $query->whereHas('user', function ($q) use ($min, $max) {
                        $q->whereBetween(DB::raw('CAST(age AS UNSIGNED)'), [$min, $max]);
                    });
                } elseif ($minAge) {
                    $query->whereHas('user', function ($q) use ($minAge) {
                        $q->where(DB::raw('CAST(age AS UNSIGNED)'), '>=', $minAge);
                    });
                } elseif ($maxAge) {
                    $query->whereHas('user', function ($q) use ($maxAge) {
                        $q->where(DB::raw('CAST(age AS UNSIGNED)'), '<=', $maxAge);
                    });
                }



                // Filter by job category if provided
                if (!empty($job_category_ids)) {
                    $query->whereHas('employee_job_categories', function ($q) use ($job_category_ids) {
                        $q->whereIn('job_category_id', $job_category_ids);
                    });
                }


                // Filter by years of experience if provided

                $minEx = min($minExperience, $maxExperience);
                $maxEx = max($minExperience, $maxExperience);
                
                if (isset($minExperience) && isset($maxExperience)) {
                    $query->whereBetween(DB::raw('CAST(year_of_experice AS UNSIGNED)'), [$minEx, $maxEx]);
                } elseif (isset($minExperience)) {
                    $query->where(DB::raw('CAST(year_of_experice AS UNSIGNED)'), '>=', $minExperience);
                } elseif (isset($maxExperience)) {
                    $query->where(DB::raw('CAST(year_of_experice AS UNSIGNED)'), '<=', $maxExperience);
                }
                



                // Filter by job category if provided
                if (!empty($job_category_ids)) {
                    $query->whereHas('employee_job_categories', function ($q) use ($job_category_ids) {
                        $q->whereIn('job_category_id', $job_category_ids);
                    });
                }


                // Filter by certification if provided
                if ($certification) {
                    $query->whereHas('certifications', function ($q) use ($certification) {
                        $q->where('name', 'LIKE', '%' . $certification . '%')
                            ->orWhere('issue_organization', 'LIKE', '%' . $certification . '%')
                            ->orWhere('creadential_id', 'LIKE', '%' . $certification . '%');
                    });
                }
            });
        }

        // Get the filtered applicants
        $applicants = $applicantsQuery
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('address', 'LIKE', '%' . $search . '%')
                        ->orWhere('cell_number', 'LIKE', '%' . $search . '%');
                });
            })

            ->get();

        if ($applicants->isEmpty()) {
            return $this->success([], 'No applicants found for the given criteria.');
        }

        // Format the applicants data
        $formattedApplicants = $applicants->map(function ($applicant) {
            $job = $applicant->job;
            $firstExperience = $applicant->employee->experiences->first();

            return [
                'id' => $applicant->id,
                'job_title' => $job->title,
                'full_name' => $applicant->full_name,
                'email' => $applicant->email,
                'cell_number' => $applicant->cell_number,
                'address' => $applicant->address,
                'resume' => url($applicant->resume),
                'employee' => [
                    'id' => $applicant->employee->id,
                    'avatar' => $applicant->employee->user->avatar ? url($applicant->employee->user->avatar) : null,
                    'age' => $applicant->employee->user->age ? (int)$applicant->employee->user->age : null,
                    'years_of_experience' => $applicant->employee->year_of_experice ? (int)$applicant->employee->year_of_experice  : null,
                ],
            ];
        });

        return $this->success([
            'applicants' => $formattedApplicants,
        ], 'Job applicants retrieved successfully.');
    }





    // Calculate years of experience
    private function calculateYearsOfExperience($experience)
    {
        if (!$experience) return 0;

        $start = Carbon::parse($experience->start_date);
        $end = $experience->end_date ? Carbon::parse($experience->end_date) : now();

        return floor($start->diffInYears($end));
    }


    public function jobApplicantDetails($employeeId)
    {
        $company = $this->getAuthenticatedCompany();

        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }


        $applicant = $company->companyJobs()
            ->whereHas('jobApplicants', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->first()
            ->jobApplicants()
            ->where('employee_id', $employeeId)
            ->with(['employee.experiences', 'employee.user'])
            ->first();

        if (!$applicant) {
            return $this->error([], 'Applicant not found.', 404);
        }

        $firstExperience = $applicant->employee->experiences->first();

        $data = [
            'id' => $applicant->id,
            'full_name' => $applicant->full_name,
            'email' => $applicant->email,
            'cell_number' => $applicant->cell_number,
            'address' => $applicant->address,
            'resume' => $applicant->resume ? url($applicant->resume) : null,

            'employee' => [
                'id' => $applicant->employee->id,
                'user_id' => $applicant->employee->user->id,
                'avatar' => $applicant->employee->user->avatar ? url($applicant->employee->user->avatar) : null,
                'age' => optional($applicant->employee->user)->date_of_birth
                    ? Carbon::parse($applicant->employee->user->date_of_birth)->age
                    : null,
                'years_of_experience' => $applicant->employee->year_of_experice
                    ? (int)$applicant->employee->year_of_experice
                    : null,

            ],
        ];

        return $this->success($data, 'Applicant details retrieved successfully.');
    }


    public function employee_details($id)
    {

        $employee = Employee::with(['experiences', 'certifications', 'specializations', 'qualifications', 'employee_job_categories'])

            ->where('id', $id)
            ->first();


        if (!$employee) {
            return $this->error('Employee profile not found.', 404);
        }


        $yearsOfExperience = $this->calculateYearsOfExperience($employee->experiences->first());

        $user = auth('api')->user();

        $data = [
            'user_id' => auth('api')->user()->id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->user->name,
            'location' => $employee->location,
            'bio' => $employee->bio,
            'image_url' => $employee->image_url ? url($employee->image_url) : null,
            'years_of_experience' => $employee->year_of_experice
                    ? (int)$employee->year_of_experice
                    : null,
            'age' => $employee->age ? (int)$employee->age : null,

            'specializations' => $employee->specializations->map(function ($spe) {

                $specialize = $spe->specialize;

                return [
                    'id' => $specialize->id,
                    'name' => $specialize->name,


                ];
            }),


            'experiences' => $employee->experiences->map(function ($exp) {
                return [
                    'id' => $exp->id,
                    'company_name' => $exp->company_name,
                    'job_title' => $exp->job_title,
                    'start_date' => $exp->start_date,
                    'end_date' => $exp->end_date,
                    'job_type' => $exp->job_type,
                    'job_location' => $exp->job_location,
                ];
            }),

            'qualifications' => $employee->qualifications->map(function ($exp) {
                return [
                    'id' => $exp->id,
                    'institute_name' => $exp->institute_name,
                    'qaulification' => $exp->qaulification,
                    'start_date' => $exp->start_date,
                    'end_date' => $exp->end_date,
                    'description' => $exp->description,

                ];
            }),

            'certifications' => $employee->certifications->map(function ($cert) {
                return [
                    'name' => $cert->name,
                    'date_issue' => $cert->date_issue,
                    'issue_organization' => $cert->issue_organization,
                    'credential_id' => $cert->creadential_id,
                ];
            }),

            'employee_job_categories' => $employee->employee_job_categories->map(function ($job_cate) {

                $category = $job_cate->job_category;

                return [
                    'id' => $category->id,
                    'name' => $category->title,


                ];
            }),
        ];


        return $this->success($data, 'Employee profile fetched successfully.', 200);
    }
}
