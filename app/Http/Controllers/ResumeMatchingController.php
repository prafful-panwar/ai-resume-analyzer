<?php

namespace App\Http\Controllers;

use App\DTO\AnalyzeResumeData;
use App\Http\Requests\AnalyzeResumeWithJdRequest;
use App\Models\JobDescription;
use App\Models\User;
use App\Repositories\Contracts\JobDescriptionRepositoryInterface;
use App\Services\ResumeAnalysisService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ResumeMatchingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ResumeAnalysisService $analysisService,
        private JobDescriptionRepositoryInterface $jobDescriptionRepository
    ) {}

    /**
     * Display the resume matching page.
     */
    public function index(): Response
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $jobDescriptions = $this->jobDescriptionRepository->getRecentForUser(
            $user,
            ['id', 'job_role', 'experience_min', 'experience_max', 'description']
        );

        return Inertia::render('ResumeMatching', [
            'jobDescriptions' => $jobDescriptions,
        ]);
    }

    /**
     * Analyze a resume against a job description.
     */
    public function analyze(AnalyzeResumeWithJdRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            if (! $user instanceof User) {
                return back()->with('error', 'Unauthorized');
            }

            $jobDescriptionId = $request->input('job_description_id');
            /** @var JobDescription|null $jobDescription */
            $jobDescription = $this->jobDescriptionRepository->findById($jobDescriptionId);

            if (! $jobDescription) {
                // Should never happen due to FormRequest validation, but satisfies static analysis
                throw new Exception('Job description not found.');
            }

            $dto = AnalyzeResumeData::fromArray($request->validated());

            // Delegate to service
            $analysis = $this->analysisService->createAnalysis(
                $user,
                $jobDescription,
                $dto
            );

            return redirect()->route('resume-analyses.show', $analysis)
                ->with('success', 'Resume analysis queued! You will be notified when it completes.');

        } catch (Exception $e) {
            Log::error('Failed to queue analysis: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'An unexpected error occurred while queuing the analysis. Please try again or contact support.');
        }
    }
}
