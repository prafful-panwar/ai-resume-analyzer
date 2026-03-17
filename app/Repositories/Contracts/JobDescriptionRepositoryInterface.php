<?php

namespace App\Repositories\Contracts;

use App\DTO\JobDescriptionData;
use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Support\Collection;

interface JobDescriptionRepositoryInterface
{
    /**
     * Get all job descriptions for a user
     *
     * @return Collection<int, JobDescription>
     */
    public function getForUser(User $user): Collection;

    /**
     * Get recent job descriptions for a user with specific columns
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, JobDescription>
     */
    public function getRecentForUser(User $user, array $columns = ['*']): Collection;

    /**
     * Find a job description by ID
     */
    public function findById(int $id): ?JobDescription;

    /**
     * Create a new job description for a user
     */
    public function createForUser(User $user, JobDescriptionData $data): JobDescription;

    /**
     * Update a job description
     */
    public function updateJob(JobDescription $jobDescription, JobDescriptionData $data): bool;

    /**
     * Delete a job description
     */
    public function delete(JobDescription $jobDescription): bool;
}
