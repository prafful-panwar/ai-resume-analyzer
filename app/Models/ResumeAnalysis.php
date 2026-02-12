<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'job_description_id',
        'resume_file_path',
        'original_filename',
        'status',
        'result',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'error_message',
    ];

    /**
     * Get the user that owns the analysis.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job description that was used for the analysis.
     *
     * @return BelongsTo<JobDescription, $this>
     */
    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    /**
     * Get the logs for the analysis.
     *
     * @return HasMany<ResumeAnalysisLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ResumeAnalysisLog::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope a query to only include high potential candidates (score >= 80).
     *
     * @param  Builder<ResumeAnalysis>  $query
     * @return Builder<ResumeAnalysis>
     */
    public function scopeHighPotential(Builder $query): Builder
    {
        return $query->where('status', 'completed')
            ->whereRaw("CAST(JSON_EXTRACT(result, '$.match_score') AS UNSIGNED) >= 80");
    }

    /**
     * Scope a query to include specific statuses.
     *
     * @param  Builder<ResumeAnalysis>  $query
     * @param  string|array<int, string>  ...$statuses
     * @return Builder<ResumeAnalysis>
     */
    public function scopeByStatus(Builder $query, string|array ...$statuses): Builder
    {
        if (count($statuses) === 1 && is_array($statuses[0])) {
            $statuses = $statuses[0];
        }

        return $query->whereIn('status', $statuses);
    }

    /**
     * Scope a query to a specific user.
     *
     * @param  Builder<ResumeAnalysis>  $query
     * @return Builder<ResumeAnalysis>
     */
    public function scopeForUser(Builder $query, int|string|null $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    protected function casts(): array
    {
        return [
            'result' => 'array',
        ];
    }
}
