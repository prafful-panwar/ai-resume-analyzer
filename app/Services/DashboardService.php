<?php

namespace App\Services;

use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private ResumeAnalysisRepositoryInterface $resumeAnalysisRepository
    ) {}

    /**
     * Get hiring statistics for the pulse cards.
     *
     * @return array{total_analyses: int, high_potentials: int, total_tokens: int, pending_count: int}
     */
    public function getHiringStats(User $user): array
    {
        $stats = $this->resumeAnalysisRepository->getStatisticsForUser($user);

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
        return $this->resumeAnalysisRepository->getRecentForUser($user, $limit);
    }

    /**
     * Get top-scoring talent leaderboard.
     *
     * @return Collection<int, ResumeAnalysis>
     */
    public function getTopTalent(User $user, int $limit = 5): Collection
    {
        return $this->resumeAnalysisRepository->getTopTalentForUser($user, $limit);
    }
}
