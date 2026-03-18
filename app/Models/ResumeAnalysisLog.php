<?php

namespace App\Models;

use Database\Factories\ResumeAnalysisLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('resume_analysis_logs')]
#[Fillable(['resume_analysis_id', 'status', 'error_message', 'result', 'attempt', 'job_uuid', 'exception_trace', 'prompt_tokens', 'completion_tokens', 'total_tokens'])]
#[UseFactory(ResumeAnalysisLogFactory::class)]
class ResumeAnalysisLog extends Model
{
    /** @use HasFactory<ResumeAnalysisLogFactory> */
    use HasFactory;

    /**
     * Get the analysis that owns the log entry.
     *
     * @return BelongsTo<ResumeAnalysis, $this>
     */
    public function resumeAnalysis(): BelongsTo
    {
        return $this->belongsTo(ResumeAnalysis::class);
    }

    protected function casts(): array
    {
        return [
            'result' => 'array',
        ];
    }
}
