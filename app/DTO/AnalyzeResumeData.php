<?php

namespace App\DTO;

use Illuminate\Http\UploadedFile;

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
        return new self(
            job_description_id: (int) ($data['job_description_id'] ?? 0),
            resume: $data['resume'],
        );
    }
}
