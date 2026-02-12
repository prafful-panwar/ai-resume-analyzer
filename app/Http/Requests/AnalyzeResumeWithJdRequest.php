<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnalyzeResumeWithJdRequest extends FormRequest
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
            'resume_file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
            'job_description_id' => ['required', 'exists:job_descriptions,id'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resume_file.required' => 'Please upload a resume PDF file.',
            'resume_file.mimes' => 'The resume must be a PDF file.',
            'resume_file.max' => 'The resume file size must not exceed 10MB.',
            'job_description_id.required' => 'Please select a job description.',
            'job_description_id.exists' => 'The selected job description does not exist.',
        ];
    }
}
