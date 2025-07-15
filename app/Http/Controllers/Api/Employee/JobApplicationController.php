<?php

namespace App\Http\Controllers\Api\Employee;

use App\Helper\Helper;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CompanyJob;
use App\Traits\ApiResponse;
use App\Models\JobApplicant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobApplicationController extends Controller
{

    use ApiResponse;
    // Method to apply for a job
    public function applyJob(Request $request)
    {
        // Validate the request data
        $request->validate([
            'job_id' => 'required|exists:company_jobs,id',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $company_job = CompanyJob::where('id', $request->job_id)->first();

        if (!$company_job) {
            return $this->error('Job not found', 404);
        }

        $company = Company::where('id', $company_job->company_id)->first();

        if (!$company) {
            return $this->error('Company not found', 404);
        }

        $employee = Employee::where('user_id', auth('api')->user()->id)->first();

        if (!$employee) {
            return $this->error('Employee not found', 404);
        }

        // Check if the employee has already applied for this job
        $existingApplication = JobApplicant::where('job_id', $request->job_id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existingApplication) {
            $existingApplication->resume = url($existingApplication->resume);
    
        } 

        if ($existingApplication) {
            return $this->success($existingApplication,'You have already applied for this job', 200);
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {

            $resume = $request->file('resume');
            $resumePath = Helper::uploadImage($resume, 'resumes');
        }
        // Create a new job application
        $applicant = JobApplicant::create([
            'company_id' => $company->id,
            'job_id' => $request->job_id,
            'employee_id' => $employee->id,
            'full_name' => $request->full_name ?  $request->full_name : auth()->user()->name ,
            'email' => $request->email ?  $request->email : auth()->user()->email,
            'cell_number' => $request->cell_number ?  $request->cell_number : auth()->user()->phone,
            'address' => $request->address ?  $request->address : auth()->user()->address,
            'resume' => $resumePath,
        ]);


        // resume url
        $applicant->resume = url($applicant->resume);


        if ($applicant) {
            return $this->success($applicant, 'Job application submitted successfully');
        } else {
            return $this->error('Failed to submit job application', 500);
        }
    }
}
