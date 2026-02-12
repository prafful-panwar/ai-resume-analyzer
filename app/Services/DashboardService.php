<?php

namespace App\Services;

use App\Models\ResumeAnalysis;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get hiring statistics for the pulse cards.
     *
     * @return array{total_analyses: int, high_potentials: int, total_tokens: int, pending_count: int}
     */
    public function getHiringStats(User $user): array
    {
        $stats = ResumeAnalysis::forUser($user->id)
            ->selectRaw('COUNT(*) as total_analyses')
            ->selectRaw("COUNT(CASE WHEN status = 'completed' AND CAST(JSON_EXTRACT(result, '$.match_score') AS UNSIGNED) >= 80 THEN 1 END) as high_potentials")
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw("COUNT(CASE WHEN status IN ('pending', 'processing') THEN 1 END) as pending_count")
            ->first();

        return [
            'total_analyses' => (int) ($stats->total_analyses ?? 0),
            'high_potentials' => (int) ($stats->high_potentials ?? 0),
            'total_tokens' => (int) ($stats->total_tokens ?? 0),
            'pending_count' => (int) ($stats->pending_count ?? 0),
        ];
    }

    /**
     * Get recent resume analysis activity.
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getRecentActivity(User $user, int $limit = 5): Collection
    {
        return ResumeAnalysis::forUser($user->id)
            ->with('jobDescription')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get top-scoring talent leaderboard.
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getTopTalent(User $user, int $limit = 5): Collection
    {
        return ResumeAnalysis::forUser($user->id)
            ->with('jobDescription')
            ->byStatus('completed')
            ->orderByRaw("CAST(JSON_EXTRACT(result, '$.match_score') AS UNSIGNED) DESC")
            ->limit($limit)
            ->get();
    }
}
