<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobDescriptionRequest extends FormRequest
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
            'job_role' => ['required', 'string', 'max:255'],
            'experience_min' => ['required', 'integer', 'min:0'],
            'experience_max' => ['required', 'integer', 'gte:experience_min'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'array'],
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
            'job_role.required' => 'The job role field is required.',
            'experience_min.required' => 'The minimum experience is required.',
            'experience_max.gte' => 'Maximum experience must be greater than or equal to minimum experience.',
            'description.required' => 'The job description is required.',
        ];
    }
}
