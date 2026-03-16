<?php

namespace App\Services;

use App\DTO\JobDescriptionData;
use App\Models\JobDescription;
use App\Models\User;
use App\Repositories\Contracts\JobDescriptionRepositoryInterface;

/**
 * Service class for handling job description operations.
 *
 * Follows Single Responsibility Principle - handles all business logic
 * related to job descriptions, keeping controllers thin.
 */
class JobDescriptionService
{
    public function __construct(
        private JobDescriptionRepositoryInterface $jobDescriptionRepository
    ) {}

    /**
     * Create a new job description for a user.
     */
    public function createJobDescription(User $user, JobDescriptionData $data): JobDescription
    {
        return $this->jobDescriptionRepository->createForUser($user, $data);
    }

    /**
     * Update an existing job description.
     */
    public function updateJobDescription(JobDescription $jobDescription, JobDescriptionData $data): bool
    {
        return $this->jobDescriptionRepository->updateJob($jobDescription, $data);
    }

    /**
     * Delete a job description.
     */
    public function deleteJobDescription(JobDescription $jobDescription): bool
    {
        return $this->jobDescriptionRepository->delete($jobDescription);
    }
}
