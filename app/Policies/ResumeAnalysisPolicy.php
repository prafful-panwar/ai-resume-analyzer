<?php

namespace App\Policies;

use App\Models\ResumeAnalysis;
use App\Models\User;

class ResumeAnalysisPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ResumeAnalysis $resumeAnalysis): bool
    {
        return $user->id === $resumeAnalysis->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ResumeAnalysis $resumeAnalysis): bool
    {
        return $user->id === $resumeAnalysis->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ResumeAnalysis $resumeAnalysis): bool
    {
        return $user->id === $resumeAnalysis->user_id;
    }
}
