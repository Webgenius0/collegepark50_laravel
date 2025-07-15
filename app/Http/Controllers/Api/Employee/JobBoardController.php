<?php

namespace App\Http\Controllers\Api\Employee;

use App\Models\Company;
use App\Models\CompanyJob;
use App\Models\JobCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\Job;
use App\Http\Controllers\Controller;

class JobBoardController extends Controller
{
    use ApiResponse;


    // feature jobs
    public function featuredJobList(Request $request)
    {
        $jobs = CompanyJob::with('company');

        $jobs->where(function ($query) use ($request) {

            
            if ($request->has('job_category_ids')) {
                $query->whereIn('job_category_id', $request->job_category_ids);
            }

          
            if ($request->filled('location')) {
                $query->where('location', 'LIKE', '%' . $request->location . '%');
            }

            
            if ($request->filled('certification')) {
                $query->where('certification', 'LIKE', '%' . $request->certification . '%');
            }
 
            if ($request->has('min_salary') && $request->has('max_salary')) {
                $salaryMin = (int) $request->min_salary;
                $salaryMax = (int) $request->max_salary;
                $query->whereBetween(DB::raw('CAST(salary AS SIGNED)'), [$salaryMin, $salaryMax]);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
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



        });

        $featuredJobs = $jobs->orderBy('created_at', 'desc')->get();

        if ($featuredJobs->isEmpty()) {
            return $this->success(null, 'No jobs found');
        }

        $recommendedJobs = $featuredJobs->shuffle();

        $data = [
            'featured_jobs' => $featuredJobs,
            'recommended_jobs' => $recommendedJobs,
        ];

        return $this->success($data, 'Jobs retrieved successfully');
    }



    public function jobDetails($id)
    {
        $job = CompanyJob::find($id);

        if (!$job) {
            return $this->error([], 'Job not found.', 404);
        }

        $company = Company::where('id', $job->company_id)->first();
        if (!$company) {
            return $this->error([], 'Company not found.', 404);
        }

        return $this->success([
            'company' => $company,
            'job' => $job,
        ], 'Job retrieved successfully.');
    }


    // recommended jobs
    public function recommendedJobList(Request $request)
    {
        $jobs = CompanyJob::with(['company'])->orderBy('created_at', 'desc')->get();

        if ($jobs->isEmpty()) {
            return $this->success('No jobs found');
        }

        return $this->success($jobs, 'Jobs retrieved successfully');
    }
}
