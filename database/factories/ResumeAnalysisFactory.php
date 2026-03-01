<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResumeAnalysis>
 */
class ResumeAnalysisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'job_description_id' => \App\Models\JobDescription::factory(),
            'resume_file_path' => 'resumes/fake_resume.pdf',
            'original_filename' => 'resume.pdf',
            'status' => 'pending',
        ];
    }
}
