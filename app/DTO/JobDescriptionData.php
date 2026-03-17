<?php

namespace App\DTO;

class JobDescriptionData
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string>|null  $requirements
     */
    public function __construct(
        public readonly string $job_role,
        public readonly int $experience_min,
        public readonly int $experience_max,
        public readonly string $description,
        public readonly ?array $requirements = null,
        public readonly array $data = []
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            job_role: $data['job_role'],
            experience_min: (int) $data['experience_min'],
            experience_max: (int) $data['experience_max'],
            description: $data['description'],
            requirements: $data['requirements'] ?? null,
            data: $data
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
