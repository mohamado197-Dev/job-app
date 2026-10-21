<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume_option' => 'required|string',
            'resume_file' => 'required_if:resume_option,new_resume|file|mimes:pdf|max:5120',
            
        ];
    }

    public function messages()
    {
        return [
            'resume_option.required' => 'Please select a resume',
            
            'resume_file.required' => 'Please upload your resume',
            'resume_file.file' => 'The file must be a resume',
            'resume_file.mimes' => 'The file must be in PDF format',
            'resume_file.max' => 'The file must not exceed 5MB',
        ];
    }
}
