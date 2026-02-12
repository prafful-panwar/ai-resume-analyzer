<?php

namespace App\Policies;

use App\Models\JobDescription;
use App\Models\User;

class JobDescriptionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobDescription $jobDescription): bool
    {
        return $user->id === $jobDescription->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobDescription $jobDescription): bool
    {
        return $user->id === $jobDescription->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobDescription $jobDescription): bool
    {
        return $user->id === $jobDescription->user_id;
    }
}
