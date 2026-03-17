<?php

namespace App\Repositories;

use App\DTO\JobDescriptionData;
use App\Models\JobDescription;
use App\Models\User;
use App\Repositories\Contracts\JobDescriptionRepositoryInterface;
use Illuminate\Support\Collection;

class JobDescriptionRepository implements JobDescriptionRepositoryInterface
{
    /**
     * Get all job descriptions for a user
     *
     * @return Collection<int, JobDescription>
     */
    public function getForUser(User $user): Collection
    {
        return $user->jobDescriptions()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent job descriptions for a user with specific columns
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, JobDescription>
     */
    public function getRecentForUser(User $user, array $columns = ['*']): Collection
    {
        return $user->jobDescriptions()
            ->latest()
            ->get($columns);
    }

    /**
     * Find a job description by ID
     */
    public function findById(int $id): ?JobDescription
    {
        return JobDescription::find($id);
    }

    /**
     * Create a new job description for a user
     */
    public function createForUser(User $user, JobDescriptionData $data): JobDescription
    {
        return $user->jobDescriptions()->create([
            'job_role' => $data->job_role,
            'experience_min' => $data->experience_min,
            'experience_max' => $data->experience_max,
            'description' => $data->description,
            'requirements' => $data->requirements,
        ]);
    }

    /**
     * Update a job description
     */
    public function updateJob(JobDescription $jobDescription, JobDescriptionData $data): bool
    {
        return $jobDescription->update([
            'job_role' => $data->job_role,
            'experience_min' => $data->experience_min,
            'experience_max' => $data->experience_max,
            'description' => $data->description,
            'requirements' => $data->requirements,
        ]);
    }

    /**
     * Delete a job description
     */
    public function delete(JobDescription $jobDescription): bool
    {
        return (bool) $jobDescription->delete();
    }
}
