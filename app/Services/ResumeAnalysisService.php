<?php

namespace App\Services;

use App\Ai\Agents\ResumeAnalystAgent;
use App\DTO\AnalyzeResumeData;
use App\Jobs\AnalyzeResumeJob;
use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Notifications\ResumeAnalysisCompleted;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Smalot\PdfParser\Parser;

/**
 * Service class for handling resume analysis operations.
 *
 * Follows Single Responsibility Principle - handles all business logic
 * related to resume analysis, keeping controllers thin.
 */
class ResumeAnalysisService
{
    public function __construct(
        private ResumeAnalysisRepositoryInterface $resumeAnalysisRepository
    ) {}

    /**
     * Setup the initial analysis record and store the file.
     */
    private function setupAnalysisRecord(User $user, JobDescription $jobDescription, AnalyzeResumeData $data): ResumeAnalysis
    {
        $filePath = $this->storeResumeFile($data->resume);
        $sanitizedFilename = $this->sanitizeFilename($data->resume->getClientOriginalName());

        return $this->resumeAnalysisRepository->createForUser(
            $user,
            $jobDescription,
            $filePath,
            $sanitizedFilename
        );
    }

    /**
     * Create a new resume analysis and dispatch it to the queue.
     */
    public function createAnalysis(User $user, JobDescription $jobDescription, AnalyzeResumeData $data): ResumeAnalysis
    {
        $analysis = $this->setupAnalysisRecord($user, $jobDescription, $data);
        $this->dispatchAnalysisJob($analysis);

        return $analysis;
    }

    /**
     * Create and perform analysis synchronously (e.g., for API calls).
     */
    public function analyzeSynchronously(User $user, JobDescription $jobDescription, AnalyzeResumeData $data): ResumeAnalysis
    {
        $analysis = $this->setupAnalysisRecord($user, $jobDescription, $data);

        return $this->performAnalysis($analysis);
    }

    /**
     * Retry a failed analysis.
     *
     * @throws Exception
     */
    public function retryAnalysis(ResumeAnalysis $analysis, bool $force = false): ResumeAnalysis
    {
        if ($analysis->status !== 'failed' && ! $force) {
            throw new Exception('Only failed analyses can be retried.');
        }

        // Log the current state before retrying
        $this->resumeAnalysisRepository->logRetry($analysis);

        // Reset status and clear error
        $this->resumeAnalysisRepository->resetForRetry($analysis);

        // Re-dispatch the job
        $this->dispatchAnalysisJob($analysis);

        $fresh = $this->resumeAnalysisRepository->findById($analysis->id);

        if (! $fresh instanceof ResumeAnalysis) {
            throw new Exception('Failed to reload analysis record.');
        }

        return $fresh;
    }

    /**
     * Get the file path for downloading a resume.
     *
     * @throws Exception
     */
    public function getResumeFilePath(ResumeAnalysis $analysis): string
    {
        $filePath = Storage::path($analysis->resume_file_path);

        if (! file_exists($filePath)) {
            throw new Exception('Resume file not found');
        }

        return $filePath;
    }

    /**
     * Get the download filename for a resume.
     */
    public function getDownloadFilename(ResumeAnalysis $analysis): string
    {
        return $analysis->original_filename;
    }

    /**
     * Store the uploaded resume file.
     *
     * @throws Exception
     */
    private function storeResumeFile(UploadedFile $file): string
    {
        $path = $file->store('resumes', 'local');

        if ($path === false) {
            throw new Exception('Failed to store resume file.');
        }

        return $path;
    }

    /**
     * Dispatch the analysis job to the queue.
     */
    private function dispatchAnalysisJob(ResumeAnalysis $analysis): void
    {
        AnalyzeResumeJob::dispatch($analysis);
    }

    /**
     * Perform the actual AI analysis for a resume.
     *
     * This method centralizes all AI-related logic, including PDF parsing,
     * token tracking, and robust JSON extraction. It can be called
     * synchronously or from a background job.
     */
    public function performAnalysis(ResumeAnalysis $analysis): ResumeAnalysis
    {
        $attemptNumber = $this->resumeAnalysisRepository->getNextAttemptNumber($analysis);

        // Update status to processing
        $this->resumeAnalysisRepository->markAsProcessing($analysis);

        try {
            // Parse PDF
            $parser = app(Parser::class);
            $filePath = Storage::path($analysis->resume_file_path);
            $pdf = $parser->parseFile($filePath);
            $resumeText = $pdf->getText();

            /** @var JobDescription|null $jobDescription */
            $jobDescription = $analysis->jobDescription;

            if (! $jobDescription) {
                throw new Exception('Associated job description not found for analysis ID: '.$analysis->id);
            }

            $agent = app()->makeWith(ResumeAnalystAgent::class, [
                'jobDescription' => $jobDescription,
                'resumeText' => $resumeText,
            ]);

            $response = $agent->prompt('Analyze this resume.');

            $matchingData = ($response instanceof StructuredAgentResponse)
                ? $response->structured
                : $this->extractJson($response->text);

            if ($matchingData === []) {
                throw new Exception('AI analysis failed to return valid JSON. Raw response: '.Str::limit($response->text, 500));
            }

            $usage = $response->usage;
            $promptTokens = $usage->promptTokens ?? 0;
            $completionTokens = $usage->completionTokens ?? 0;
            $totalTokens = $promptTokens + $completionTokens;

            $matchingData['job_description'] = [
                'id' => $jobDescription->id,
                'job_role' => $jobDescription->job_role,
                'experience_range' => "{$jobDescription->experience_min}-{$jobDescription->experience_max} years",
            ];

            // Finalize analysis as completed via Repo
            $this->resumeAnalysisRepository->markAsCompleted(
                $analysis,
                $matchingData,
                $promptTokens,
                $completionTokens,
                $totalTokens,
                $attemptNumber
            );

            $this->notifyUserOfCompletion($analysis);

            return $analysis;

        } catch (Exception $e) {
            // Finalize analysis as failed via Repo
            $this->resumeAnalysisRepository->markAsFailed(
                $analysis,
                Str::limit($e->getMessage(), 500),
                $attemptNumber
            );

            $this->notifyUserOfCompletion($analysis);

            throw $e;
        }
    }

    /**
     * Notify the user about analysis completion or failure.
     */
    private function notifyUserOfCompletion(ResumeAnalysis $analysis): void
    {
        $user = $analysis->user;

        if ($user) {
            $user->notify(new ResumeAnalysisCompleted($analysis));
        } else {
            Log::warning('Could not notify user: User not found for analysis ID: '.$analysis->id);
        }
    }

    /**
     * Extract JSON from the AI response.
     *
     * @return array<string, mixed>
     */
    private function extractJson(string $text): array
    {
        // Try to find JSON block in markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
            // Try to find the first JSON-like structure
            $json = $matches[0];
        } else {
            $json = $text;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Sanitize filename to prevent security issues.
     *
     * Removes special characters, limits length, preserves extension.
     */
    private function sanitizeFilename(string $filename): string
    {
        // Get file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Sanitize basename
        $basename = Str::of($basename)
            ->replaceMatches('/[^a-zA-Z0-9\s_-]/', '')
            ->squish()
            ->trim()
            ->substr(0, 100)
            ->toString();

        // If basename is empty after sanitization, use a default
        if ($basename === '' || $basename === '0') {
            $basename = 'resume_'.now()->timestamp;
        }

        // Sanitize extension (only allow common document formats)
        $allowedExtensions = collect(['pdf', 'doc', 'docx', 'txt']);
        $extension = Str::lower($extension);
        if ($allowedExtensions->doesntContain($extension)) {
            $extension = 'pdf'; // Default to pdf
        }

        return $basename.'.'.$extension;
    }
}
