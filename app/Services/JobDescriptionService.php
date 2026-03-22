<?php

namespace App\Services;

use App\DTO\JobDescriptionData;
use App\Models\JobDescription;
use App\Models\User;
use App\Repositories\Contracts\JobDescriptionRepositoryInterface;

class JobDescriptionService
{
    public function __construct(
        private JobDescriptionRepositoryInterface $jobDescriptionRepository
    ) {}

    public function createJobDescription(User $user, JobDescriptionData $data): JobDescription
    {
        return $this->jobDescriptionRepository->createForUser($user, $data);
    }

    public function updateJobDescription(JobDescription $jobDescription, JobDescriptionData $data): bool
    {
        return $this->jobDescriptionRepository->updateJob($jobDescription, $data);
    }

    public function deleteJobDescription(JobDescription $jobDescription): bool
    {
        return $this->jobDescriptionRepository->delete($jobDescription);
    }
}
