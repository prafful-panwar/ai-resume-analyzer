<?php

namespace App\Services;

use App\Ai\Agents\ResumeAnalystAgent;
use App\Jobs\AnalyzeResumeJob;
use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
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
    /**
     * Create a new resume analysis and dispatch it to the queue.
     */
    public function createAnalysis(User $user, JobDescription $jobDescription, UploadedFile $resumeFile): ResumeAnalysis
    {
        // Store the resume file
        $filePath = $this->storeResumeFile($resumeFile);

        // Sanitize the original filename for security
        $sanitizedFilename = $this->sanitizeFilename($resumeFile->getClientOriginalName());

        // Create analysis record
        $analysis = $user->resumeAnalyses()->create([
            'job_description_id' => $jobDescription->id,
            'resume_file_path' => $filePath,
            'original_filename' => $sanitizedFilename,
            'status' => 'pending',
        ]);

        // Dispatch job to queue
        $this->dispatchAnalysisJob($analysis);

        return $analysis;
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
        $analysis->logs()->create([
            'status' => $analysis->status,
            'error_message' => $analysis->error_message,
            'result' => $analysis->result,
            'attempt' => $analysis->logs()->count() + 1,
        ]);

        // Reset status and clear error
        $analysis->update([
            'status' => 'pending',
            'error_message' => null,
            'result' => null,
        ]);

        // Re-dispatch the job
        $this->dispatchAnalysisJob($analysis);

        $fresh = $analysis->fresh();

        if (! $fresh) {
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
        $attemptNumber = $analysis->logs()->count() + 1;

        // Update status to processing
        $analysis->update(['status' => 'processing']);

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

        $analysis->update([
            'status' => 'completed',
            'result' => $matchingData,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
        ]);

        $analysis->logs()->create([
            'status' => 'completed',
            'result' => $matchingData,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'attempt' => $attemptNumber,
        ]);

        return $analysis;
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
        $extension = strtolower($extension);
        if ($allowedExtensions->doesntContain($extension)) {
            $extension = 'pdf'; // Default to pdf
        }

        return $basename.'.'.$extension;
    }
}
