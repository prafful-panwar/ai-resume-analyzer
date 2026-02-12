<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeResumeWithJdRequest;
use App\Models\JobDescription;
use App\Models\User;
use App\Services\ResumeAnalysisService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class ResumeMatchingController extends Controller
{
    /**
     * Analyze resume against job description
     */
    /**
     * Analyze resume against job description
     */
    public function analyze(AnalyzeResumeWithJdRequest $request, ResumeAnalysisService $analysisService): JsonResponse
    {
        try {
            $user = $request->user();
            if (! $user instanceof User) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Verify user owns the JD
            $jobDescriptionId = $request->input('job_description_id');
            /** @var JobDescription $jobDescription */
            $jobDescription = JobDescription::findOrFail($jobDescriptionId);

            if ($jobDescription->user_id !== $user->id) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            // 1. Create the analysis record (and store the file)
            $analysis = $analysisService->createAnalysis(
                $user,
                $jobDescription,
                /** @var UploadedFile $resumeFile */
                $resumeFile = $request->file('resume_file')
            );

            // 2. Perform analysis synchronously for the API response
            // We use the service method to ensure identical logic (PDF parsing, AI prompt, etc.)
            $analysisService->performAnalysis($analysis);

            return response()->json([
                'success' => true,
                'data' => (array) $analysis->result,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze resume',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
