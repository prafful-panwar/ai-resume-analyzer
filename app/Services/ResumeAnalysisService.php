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

class ResumeAnalysisService
{
    public function __construct(
        private ResumeAnalysisRepositoryInterface $resumeAnalysisRepository,
        private ResumeScoringService $scoringService
    ) {}

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

    public function createAnalysis(User $user, JobDescription $jobDescription, AnalyzeResumeData $data): ResumeAnalysis
    {
        $analysis = $this->setupAnalysisRecord($user, $jobDescription, $data);
        $this->dispatchAnalysisJob($analysis);

        return $analysis;
    }

    public function analyzeSynchronously(User $user, JobDescription $jobDescription, AnalyzeResumeData $data): ResumeAnalysis
    {
        $analysis = $this->setupAnalysisRecord($user, $jobDescription, $data);

        return $this->performAnalysis($analysis);
    }

    /**
     * @throws Exception
     */
    public function retryAnalysis(ResumeAnalysis $analysis, bool $force = false): ResumeAnalysis
    {
        throw_if($analysis->status !== 'failed' && ! $force, Exception::class, 'Only failed analyses can be retried.');

        $this->resumeAnalysisRepository->logRetry($analysis);

        $this->resumeAnalysisRepository->resetForRetry($analysis);

        $this->dispatchAnalysisJob($analysis);

        $fresh = $this->resumeAnalysisRepository->findById($analysis->id);

        throw_unless($fresh instanceof ResumeAnalysis, Exception::class, 'Failed to reload analysis record.');

        return $fresh;
    }

    /**
     * @throws Exception
     */
    public function getResumeFilePath(ResumeAnalysis $analysis): string
    {
        $filePath = Storage::path($analysis->resume_file_path);

        throw_unless(file_exists($filePath), Exception::class, 'Resume file not found');

        return $filePath;
    }

    public function getDownloadFilename(ResumeAnalysis $analysis): string
    {
        return $analysis->original_filename;
    }

    /**
     * @throws Exception
     */
    private function storeResumeFile(UploadedFile $file): string
    {
        $path = $file->store('resumes', 'local');

        throw_if($path === false, Exception::class, 'Failed to store resume file.');

        return $path;
    }

    private function dispatchAnalysisJob(ResumeAnalysis $analysis): void
    {
        dispatch(new AnalyzeResumeJob($analysis));
    }

    public function performAnalysis(ResumeAnalysis $analysis): ResumeAnalysis
    {
        $attemptNumber = $this->resumeAnalysisRepository->getNextAttemptNumber($analysis);

        $this->resumeAnalysisRepository->markAsProcessing($analysis);

        try {
            $parser = resolve(Parser::class);
            $filePath = Storage::path($analysis->resume_file_path);
            $pdf = $parser->parseFile($filePath);
            $resumeText = $pdf->getText();

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

            $scoreData = $this->scoringService->calculateScore($matchingData);

            $matchingData['match_score'] = $scoreData['score'];
            $matchingData['score'] = $scoreData['score'];
            $matchingData['recommendation'] = $scoreData['recommendation'];
            $matchingData['matched_primary_skills'] = $scoreData['matched_primary_skills'];
            $matchingData['missing_primary_skills'] = $scoreData['missing_primary_skills'];
            $matchingData['summary'] = $scoreData['summary'];

            $usage = $response->usage;
            $promptTokens = $usage->promptTokens ?? 0;
            $completionTokens = $usage->completionTokens ?? 0;
            $totalTokens = $promptTokens + $completionTokens;

            $matchingData['job_description'] = [
                'id' => $jobDescription->id,
                'job_role' => $jobDescription->job_role,
                'experience_range' => "{$jobDescription->experience_min}-{$jobDescription->experience_max} years",
            ];

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
            $this->resumeAnalysisRepository->markAsFailed(
                $analysis,
                Str::limit($e->getMessage(), 500),
                $attemptNumber
            );

            $this->notifyUserOfCompletion($analysis);

            throw $e;
        }
    }

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
     * @return array<string, mixed>
     */
    private function extractJson(string $text): array
    {
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
            $json = $matches[0];
        } else {
            $json = $text;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        $basename = Str::of($basename)
            ->replaceMatches('/[^a-zA-Z0-9\s_-]/', '')
            ->squish()
            ->trim()
            ->substr(0, 100)
            ->toString();

        if ($basename === '' || $basename === '0') {
            $basename = 'resume_'.now()->timestamp;
        }

        $allowedExtensions = collect(['pdf', 'doc', 'docx', 'txt']);
        $extension = Str::lower($extension);
        if ($allowedExtensions->doesntContain($extension)) {
            $extension = 'pdf';
        }

        return $basename.'.'.$extension;
    }
}
