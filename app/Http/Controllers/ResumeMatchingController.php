<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeResumeWithJdRequest;
use App\Models\JobDescription;
use App\Models\User;
use App\Services\ResumeAnalysisService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class ResumeMatchingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ResumeAnalysisService $analysisService
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

        $jobDescriptions = $user->jobDescriptions()
            ->latest()
            ->get(['id', 'job_role', 'experience_min', 'experience_max', 'description']);

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

            // Verify user owns the JD
            $jobDescriptionId = $request->input('job_description_id');
            /** @var JobDescription $jobDescription */
            $jobDescription = JobDescription::findOrFail($jobDescriptionId);

            $this->authorize('view', $jobDescription);

            // Delegate to service
            $analysis = $this->analysisService->createAnalysis(
                $user,
                $jobDescription,
                /** @var UploadedFile $resumeFile */
                $resumeFile = $request->file('resume_file')
            );

            return redirect()->route('resume-analyses.show', $analysis)
                ->with('success', 'Resume analysis queued! You will be notified when it completes.');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to queue analysis: '.$e->getMessage());
        }
    }
}
