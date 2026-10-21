<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI;
use Spatie\PdfToText\Pdf;

class ResumeAnalysisService
{
    public function extractResumeInformation(string $fileUrl){
        try {
            $rawText = $this->extractTextFromPdf($fileUrl);
            Log::info('Successfully extracted text from pdf: ' . strlen($rawText) . ' characters');

            // الاتصال بأحدث موديل من Gemini عبر HTTP
                $response = \Illuminate\Support\Facades\Http::post('https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=' . env('GEMINI_API_KEY'), ['system_instruction' => [
                    'parts' => [
                        ['text' => 'You are a precise resume parser. Extract information exactly as it appears in the resume without adding any interpretation or additional information. The output must be in strict JSON format.']
                    ]
                ],
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Parse the following resume content and extract the information as a JSON object with the exact keys: 'summary', 'skills', 'education', 'experience'. The resume content is:\n{$rawText}\nReturn an empty string for any key that is not found."]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0,
                    'responseMimeType' => 'application/json' // إجبار الموديل على إرجاع JSON نظيف فقط
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Request Failed: ' . $response->body());
                throw new \Exception('Failed to communicate with Gemini API');
            }

            // استخراج النص (JSON) من هيكل استجابة Gemini
            $result = $response->json('candidates.0.content.parts.0.text');
            Log::info('Gemini API response: ' . $result);

            $parsedResult = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse AI response: ' . json_last_error_msg());
                throw new \Exception('Failed to parse AI response');
            }

            // التحقق من وجود المفاتيح المطلوبة
            $requiredKeys = ['summary', 'skills', 'education', 'experience'];
            $missingKeys = array_diff($requiredKeys, array_keys($parsedResult));
            
            if (count($missingKeys) > 0) {
                Log::error('Missing keys in AI response: ' . implode(', ', $missingKeys));
                throw new \Exception('Missing keys in AI response');
            }

            return [
                'summary' => is_array($parsedResult['summary']) ? implode("\n", $parsedResult['summary']) : ($parsedResult['summary'] ?? ''),
                'skills' => is_array($parsedResult['skills']) ? implode(', ', $parsedResult['skills']) : ($parsedResult['skills'] ?? ''),
                'education' => is_array($parsedResult['education']) ? json_encode($parsedResult['education'], JSON_UNESCAPED_UNICODE) : ($parsedResult['education'] ?? ''),
                'experience' => is_array($parsedResult['experience']) ? json_encode($parsedResult['experience'], JSON_UNESCAPED_UNICODE) : ($parsedResult['experience'] ?? ''),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to extract resume information: ' . $e->getMessage());
            return [
                'summary' => '',
                'skills' => '',
                'education' => '',
                'experience' => ''
            ];
        }
    }

    public function analyzeResume($jobVacancy , $resumeData){
        try {
            $jobDetails = json_encode(
                [
                    'job_title' => $jobVacancy->title,
                    'job_description' => $jobVacancy->description,
                    'job_location' => $jobVacancy->location,
                    'job_salary' => $jobVacancy->salary,
                    'job_type' => $jobVacancy->type,
                ], JSON_UNESCAPED_UNICODE);

            $resumeDetails = json_encode($resumeData, JSON_UNESCAPED_UNICODE);

            // الاتصال بـ Gemini API عبر HTTP
            $response = \Illuminate\Support\Facades\Http::post('https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=' . env('GEMINI_API_KEY'), [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => "You are an expert HR professional and job recruiter. You are given a job vacancy and a resume. Your task is to analyze the resume and determine if the candidate is a good fit for the job. Provide a score from 0 to 100 for the candidate's suitability for the job, and a detailed feedback. The response must be in strict JSON format only, with the exact keys: 'aiGeneratedScore' and 'aiGeneratedFeedback'. The feedback should be detailed and specific to the job and the candidate's resume."]
                        ]
                    ],
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "Please evaluate this job application.\n\nJob Vacancy:\n{$jobDetails}\n\nCandidate Resume:\n{$resumeDetails}"]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0,
                        'responseMimeType' => 'application/json' // إجبار الموديل على إرجاع JSON نقي
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Request Failed: ' . $response->body());
                throw new \Exception('Failed to communicate with Gemini API');
            }

            // استخراج النص من هيكل استجابة جيميناي
            $result = $response->json('candidates.0.content.parts.0.text');
            Log::info('AI response: ' . $result);

            $parsedResult = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse AI response: ' . json_last_error_msg());
                throw new \Exception('Failed to parse AI response');
            }

            if (!isset($parsedResult['aiGeneratedScore']) || !isset($parsedResult['aiGeneratedFeedback'])) {
                Log::error('Missing required keys in the parsed result');
                throw new \Exception('Missing required keys in the parsed result');
            }

            return $parsedResult;

        } catch (\Exception $e) 
        {
            Log::error('Failed to analyze resume: ' . $e->getMessage());
            return [
                'aiGeneratedScore' => 0,
                'aiGeneratedFeedback' => $e->getMessage()
            ];
                    
        }
    }

    private function extractTextFromPdf(string $fileUrl): string {
        // 1. إنشاء ملف مؤقت بصيغة pdf
        $tempFile = tempnam(sys_get_temp_dir(), 'resume') . '.pdf';

        // 2. قراءة محتوى الملف مباشرة من الرابط السحابي (بدون استخدام Storage)
        $pdfContent = file_get_contents($fileUrl);

        if(!$pdfContent){
            throw new \Exception("Failed to read PDF content from Cloudinary URL");
        }
        
        // 3. وضع المحتوى داخل الملف المؤقت
        file_put_contents($tempFile, $pdfContent);

        // 4. التأكد من وجود أداة pdftotext
        $pdfToTextPath = [
            '/opt/homebrew/bin/pdftotext',
            '/usr/bin/pdftotext',
            '/usr/local/bin/pdftotext',
            'C:\Program Files\xpdf\bin\pdftotext.exe' // أضفنا مسار الويندوز إذا كنت تستخدم بيئة Herd على الويندوز
        ];

        $pdfToTextAvailable = false;
        foreach ($pdfToTextPath as $path) {
            if (file_exists($path)) {
                $pdfToTextAvailable = true;
                break;
            }
        }

        if (!$pdfToTextAvailable) {
            throw new \Exception("pdftotext is not installed in the specified paths");
        }

        // 5. استخراج النص
        $instance = new \Spatie\PdfToText\Pdf();
        $instance->setPdf($tempFile);
        $text = $instance->text();

        // 6. حذف الملف المؤقت لتنظيف المساحة
        unlink($tempFile);

        return $text;
    }
}