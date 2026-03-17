<?php

namespace App\Repositories;

use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use Illuminate\Support\Collection;

class ResumeAnalysisRepository implements ResumeAnalysisRepositoryInterface
{
    /**
     * Get all analyses for a user
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getForUser(User $user): Collection
    {
        return $user->resumeAnalyses()
            ->with('jobDescription')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent analyses for a user
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getRecentForUser(User $user, int $limit = 5): Collection
    {
        return ResumeAnalysis::forUser($user->id)
            ->with('jobDescription')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics for a user's resume analyses
     */
    public function getStatisticsForUser(User $user): object
    {
        $analyses = ResumeAnalysis::forUser($user->id)->get(['status', 'result', 'total_tokens']);

        $totalTokens = $analyses->sum('total_tokens');

        $highPotentials = $analyses->filter(
            fn (ResumeAnalysis $analysis): bool => $analysis->status === 'completed'
                && isset($analysis->result['match_score'])
                && (int) $analysis->result['match_score'] >= 80
        )->count();

        $pendingCount = $analyses->filter(
            fn (ResumeAnalysis $analysis): bool => in_array($analysis->status, ['pending', 'processing'], true)
        )->count();

        return (object) [
            'total_analyses' => $analyses->count(),
            'high_potentials' => $highPotentials,
            'total_tokens' => $totalTokens,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Get top-scoring talent for a user
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getTopTalentForUser(User $user, int $limit = 5): Collection
    {
        return ResumeAnalysis::forUser($user->id)
            ->with('jobDescription')
            ->byStatus('completed')
            ->get()
            ->sortByDesc(fn (ResumeAnalysis $analysis): int => (int) ($analysis->result['match_score'] ?? 0))
            ->take($limit)
            ->values();
    }

    /**
     * Find an analysis by ID
     */
    public function findById(int $id): ?ResumeAnalysis
    {
        return ResumeAnalysis::find($id);
    }

    /**
     * Create a new analysis record for a user
     */
    public function createForUser(User $user, JobDescription $jobDescription, string $filePath, string $originalFilename): ResumeAnalysis
    {
        return $user->resumeAnalyses()->create([
            'job_description_id' => $jobDescription->id,
            'resume_file_path' => $filePath,
            'original_filename' => $originalFilename,
            'status' => 'pending',
        ]);
    }

    /**
     * Get the next attempt number for an analysis
     */
    public function getNextAttemptNumber(ResumeAnalysis $analysis): int
    {
        return $analysis->logs()->count() + 1;
    }

    /**
     * Mark analysis as processing
     */
    public function markAsProcessing(ResumeAnalysis $analysis): bool
    {
        return $analysis->update(['status' => 'processing']);
    }

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
    ): bool {
        return $analysis->getConnection()->transaction(function () use (
            $analysis,
            $result,
            $promptTokens,
            $completionTokens,
            $totalTokens,
            $attemptNumber
        ): bool {
            $analysis->update([
                'status' => 'completed',
                'result' => $result,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'error_message' => null,
            ]);

            $analysis->logs()->create([
                'status' => 'completed',
                'result' => $result,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
                'attempt' => $attemptNumber,
            ]);

            return true;
        });
    }

    /**
     * Mark analysis as failed and log the error
     */
    public function markAsFailed(ResumeAnalysis $analysis, string $errorMessage, int $attemptNumber): true
    {
        return $analysis->getConnection()->transaction(function () use ($analysis, $errorMessage, $attemptNumber): true {
            $analysis->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);

            $analysis->logs()->create([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'attempt' => $attemptNumber,
            ]);

            return true;
        });
    }

    /**
     * Reset analysis state for a retry
     */
    public function resetForRetry(ResumeAnalysis $analysis): bool
    {
        return $analysis->update([
            'status' => 'pending',
            'error_message' => null,
            'result' => null,
        ]);
    }

    /**
     * Log the current state before retrying
     */
    public function logRetry(ResumeAnalysis $analysis): void
    {
        $analysis->logs()->create([
            'status' => $analysis->status,
            'error_message' => $analysis->error_message,
            'result' => $analysis->result,
            'attempt' => $analysis->logs()->count() + 1,
        ]);
    }
}
