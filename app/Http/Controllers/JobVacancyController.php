<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\ApplyJobRequest;
use App\Models\Resume;
use Cloudinary\Cloudinary;
use App\Models\JobApplication;
use App\Services\ResumeAnalysisService;

class JobVacancyController extends Controller
{
    protected $resumeAnalysisService;

    public function __construct(ResumeAnalysisService $resumeAnalysisService){
        $this->resumeAnalysisService = $resumeAnalysisService;
    }
    public function show($id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        return view('job-vacancies.show', compact('jobVacancy'));
    }

    public function apply(Request $request, $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        $resumes = auth()->user()->resume;
        return view('job-vacancies.apply', compact('jobVacancy', 'resumes'));
    }

    public function processApplication(ApplyJobRequest $request, $id)
    {   
        $jobVacancy = JobVacancy::findOrFail($id);
        $resume_id= null;
        $extractedInfo= null;
        if($request->input('resume_option') === 'new_resume'){
            // resume_file from input name in html form
            $file = $request->file('resume_file');
            $extension = $file->getClientOriginalExtension();
            $originalFileName = $file->getClientOriginalName();
            $fileName = 'resume_' . time() . '.' . $extension;

            // 2. تهيئة الاتصال بـ Cloudinary باستخدام الرابط الموجود في ملف .env
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            // 3. رفع الملف إلى مجلد resumes
            $uploadedFile = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => 'resumes',
                'resource_type' => 'auto'
            ]);

            // 4. الحصول على الرابط المباشر
            $fileUrl = $uploadedFile['secure_url'];

            // TODO: extract info from resume
            $extractedInfo = $this->resumeAnalysisService->extractResumeInformation($fileUrl);
            

            $resume = Resume::create([
                'fileName' => $originalFileName,
                'fileUrl' => $fileUrl,
                'user_id' => auth()->id(),
                'contactDetails' => json_encode([
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,

                ]),
                'summary' => json_encode($extractedInfo['summary']),
                'skills' => json_encode($extractedInfo['skills']),
                'education' => json_encode($extractedInfo['education']),
                'experience' => json_encode($extractedInfo['experience']),
            ]);

            $resume_id = $resume->id;
            
        }
        else{
            $resume_id = $request->input('resume_option');
            $resume = Resume::findOrFail($resume_id);

            // If extraction previously failed (empty fields), re-extract from the stored Cloudinary URL
            if (empty($resume->summary) && empty($resume->skills) && empty($resume->education) && empty($resume->experience)) {
                $extractedInfo = $this->resumeAnalysisService->extractResumeInformation($resume->fileUrl);

                // Update the record so future applications benefit from the extracted data
                $resume->update([
                    'summary'   => $extractedInfo['summary'],
                    'skills'    => $extractedInfo['skills'],
                    'education' => $extractedInfo['education'],
                    'experience'=> $extractedInfo['experience'],
                ]);
            } else {
                $extractedInfo = [
                    'summary'   => $resume->summary,
                    'skills'    => $resume->skills,
                    'education' => $resume->education,
                    'experience'=> $resume->experience,
                ];
            }

        }

        //TODO: evaluate the application with ai
        $evaluation = $this->resumeAnalysisService->analyzeResume( $jobVacancy , $extractedInfo);
        $jobApplication = JobApplication::create([
                'resume_id' => $resume_id,
                'job_id' => $id,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'aiGeneratedScore' => $evaluation['aiGeneratedScore'],
                'aiGeneratedFeedback' => $evaluation['aiGeneratedFeedback'],
            ]);




        return redirect()->route('job-applications.index', $id)->with('success', 'Job application submitted successfully');
    }
}
