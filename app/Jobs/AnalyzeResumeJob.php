<?php

namespace App\Jobs;

use App\Models\ResumeAnalysis;
use App\Notifications\ResumeAnalysisCompleted;
use App\Services\ResumeAnalysisService;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Log;
use Throwable;

class AnalyzeResumeJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [60, 120, 300]; // 1 min, 2 min, 5 min

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addMinutes(30);
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return (string) $this->resumeAnalysis->id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('resume-analysis')];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ResumeAnalysis $resumeAnalysis
    ) {}

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'resume-analysis',
            'user:'.$this->resumeAnalysis->user_id,
            'analysis:'.$this->resumeAnalysis->id,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(ResumeAnalysisService $analysisService): void
    {
        $analysisService->performAnalysis($this->resumeAnalysis);

        $this->notifyUser();
    }

    /**
     * Handle a job failure (called after all retries exhausted).
     */
    public function failed(Throwable $exception): void
    {
        $this->logFailure($exception, true);
        $this->markAsFailed($exception->getMessage());
        $this->notifyUser();
    }

    /**
     * Mark the analysis as failed.
     */
    private function markAsFailed(string $errorMessage): void
    {
        if ($this->resumeAnalysis->status !== 'failed') {
            $this->resumeAnalysis->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);
        }
    }

    /**
     * Log failure information.
     */
    private function logFailure(Throwable $exception, bool $isPermanent = false): void
    {
        $logData = [
            'analysis_id' => $this->resumeAnalysis->id,
            'error' => $exception->getMessage(),
            'exception_class' => get_class($exception),
        ];

        // Create database log entry
        try {
            $this->resumeAnalysis->logs()->create([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'attempt' => $this->attempts(),
                'job_uuid' => $this->job ? $this->job->getJobId() : null,
                'exception_trace' => $exception->getTraceAsString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create resume analysis log entry', ['error' => $e->getMessage()]);
        }

        if ($isPermanent) {
            $logData['attempts'] = $this->attempts();
            $logData['max_tries'] = $this->tries;
            $logData['trace'] = $exception->getTraceAsString();
            Log::error('Resume analysis job failed permanently', $logData);
        } else {
            Log::warning('Resume analysis attempt failed (will retry)', $logData);
        }
    }

    /**
     * Notify the user about analysis completion or failure.
     */
    private function notifyUser(): void
    {
        $user = $this->resumeAnalysis->user;

        if ($user) {
            $user->notify(new ResumeAnalysisCompleted($this->resumeAnalysis));
        } else {
            Log::warning('Could not notify user: User not found for analysis ID: '.$this->resumeAnalysis->id);
        }
    }
}
