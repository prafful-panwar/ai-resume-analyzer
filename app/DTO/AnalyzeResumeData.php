<?php

namespace App\DTO;

use Illuminate\Http\UploadedFile;
use ValueError;

class AnalyzeResumeData
{
    public function __construct(
        public readonly int $job_description_id,
        public readonly UploadedFile $resume,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $jobDescriptionId = (int) ($data['job_description_id'] ?? 0);

        throw_if($jobDescriptionId <= 0, ValueError::class, 'job_description_id must be a positive integer.');

        return new self(
            job_description_id: $jobDescriptionId,
            resume: $data['resume'],
        );
    }
}
