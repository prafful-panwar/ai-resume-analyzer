<?php

namespace App\Repositories\Contracts;

use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use Illuminate\Support\Collection;

interface ResumeAnalysisRepositoryInterface
{
    /**
     * Get all analyses for a user
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getForUser(User $user): Collection;

    /**
     * Get recent analyses for a user
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getRecentForUser(User $user, int $limit = 5): Collection;

    /**
     * Get statistics for a user's resume analyses
     */
    public function getStatisticsForUser(User $user): object;

    /**
     * Get top-scoring talent for a user
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getTopTalentForUser(User $user, int $limit = 5): Collection;

    /**
     * Find an analysis by ID
     */
    public function findById(int $id): ?ResumeAnalysis;

    /**
     * Create a new analysis record for a user
     */
    public function createForUser(User $user, JobDescription $jobDescription, string $filePath, string $originalFilename): ResumeAnalysis;

    /**
     * Get the next attempt number for an analysis
     */
    public function getNextAttemptNumber(ResumeAnalysis $analysis): int;

    /**
     * Mark analysis as processing
     */
    public function markAsProcessing(ResumeAnalysis $analysis): bool;

    /**
     * Mark analysis as completed and log the result
     *
     * @param  array<string, mixed>  $result
     */
    public function markAsCompleted(
        ResumeAnalysis $analysis,
        array $result,
        int $promptTokens,
        int $completionTokens,
        int $totalTokens,
        int $attemptNumber
    ): bool;

    /**
     * Mark analysis as failed and log the error
     */
    public function markAsFailed(ResumeAnalysis $analysis, string $errorMessage, int $attemptNumber): bool;

    /**
     * Reset analysis state for a retry
     */
    public function resetForRetry(ResumeAnalysis $analysis): bool;

    /**
     * Log the current state before retrying
     */
    public function logRetry(ResumeAnalysis $analysis): void;
}
