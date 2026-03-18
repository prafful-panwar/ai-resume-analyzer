<?php

namespace App\DTO;

class JobDescriptionData
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string>|null  $requirements
     */
    public function __construct(
        public private(set) string $job_role,
        public private(set) int $experience_min,
        public private(set) int $experience_max,
        public private(set) string $description,
        public private(set) ?array $requirements = null,
        public private(set) array $data = []
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
