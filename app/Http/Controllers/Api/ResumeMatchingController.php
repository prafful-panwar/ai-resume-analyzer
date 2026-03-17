<?php

namespace App\Http\Controllers\Api;

use App\DTO\AnalyzeResumeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeResumeWithJdRequest;
use App\Http\Resources\ResumeAnalysisResource;
use App\Models\JobDescription;
use App\Models\User;
use App\Repositories\Contracts\JobDescriptionRepositoryInterface;
use App\Services\ResumeAnalysisService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Throwable;

class ResumeMatchingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ResumeAnalysisService $analysisService,
        private JobDescriptionRepositoryInterface $jobDescriptionRepository
    ) {}

    /**
     * Analyze resume against job description
     */
    public function analyze(AnalyzeResumeWithJdRequest $request): JsonResponse|ResumeAnalysisResource
    {
        try {
            $user = $request->user();
            if (! $user instanceof User) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $jobDescriptionId = $request->input('job_description_id');
            /** @var JobDescription|null $jobDescription */
            $jobDescription = $this->jobDescriptionRepository->findById($jobDescriptionId);

            if (! $jobDescription) {
                return response()->json(['success' => false, 'error' => 'Job description not found.'], 404);
            }

            $this->authorize('view', $jobDescription);

            $dto = AnalyzeResumeData::fromArray($request->validated());

            $analysis = $this->analysisService->analyzeSynchronously(
                $user,
                $jobDescription,
                $dto
            );

            return ResumeAnalysisResource::make($analysis);

        } catch (AuthorizationException) {
            return response()->json(['success' => false, 'error' => 'Forbidden.'], 403);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze resume.',
            ], 500);
        }
    }
}
