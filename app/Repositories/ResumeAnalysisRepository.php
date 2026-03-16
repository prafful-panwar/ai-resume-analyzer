<?php

namespace App\Repositories;

use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        return ResumeAnalysis::forUser($user->id)
            ->selectRaw('COUNT(*) as total_analyses')
            ->selectRaw("COUNT(CASE WHEN status = 'completed' AND CAST(JSON_EXTRACT(result, '$.match_score') AS UNSIGNED) >= 80 THEN 1 END) as high_potentials")
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw("COUNT(CASE WHEN status IN ('pending', 'processing') THEN 1 END) as pending_count")
            ->first() ?? (object) [
                'total_analyses' => 0,
                'high_potentials' => 0,
                'total_tokens' => 0,
                'pending_count' => 0,
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
            ->orderByRaw("CAST(JSON_EXTRACT(result, '$.match_score') AS UNSIGNED) DESC")
            ->limit($limit)
            ->get();
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
        DB::beginTransaction();

        try {
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

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark analysis as failed and log the error
     */
    public function markAsFailed(ResumeAnalysis $analysis, string $errorMessage, int $attemptNumber): bool
    {
        DB::beginTransaction();

        try {
            $analysis->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);

            $analysis->logs()->create([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'attempt' => $attemptNumber,
            ]);

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            return false;
        }
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
