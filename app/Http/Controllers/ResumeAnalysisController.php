<?php

namespace App\Http\Controllers;

use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use App\Services\ResumeAnalysisService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResumeAnalysisController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ResumeAnalysisService $analysisService,
        private ResumeAnalysisRepositoryInterface $resumeAnalysisRepository
    ) {}

    /**
     * Display a listing of the user's resume analyses.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $analyses = $this->resumeAnalysisRepository->getForUser($user);

        return Inertia::render('ResumeAnalyses/Index', [
            'analyses' => $analyses,
        ]);
    }

    /**
     * Display the specified resume analysis.
     */
    #[Authorize('view', 'resumeAnalysis')]
    public function show(ResumeAnalysis $resumeAnalysis): Response
    {
        $resumeAnalysis->load(['jobDescription', 'logs']);

        return Inertia::render('ResumeAnalyses/Show', [
            'analysis' => $resumeAnalysis,
        ]);
    }

    /**
     * Download the resume file.
     */
    #[Authorize('view', 'resumeAnalysis')]
    public function download(ResumeAnalysis $resumeAnalysis): BinaryFileResponse
    {
        try {
            $filePath = $this->analysisService->getResumeFilePath($resumeAnalysis);
            $downloadName = $this->analysisService->getDownloadFilename($resumeAnalysis);

            return response()->download($filePath, $downloadName);
        } catch (Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Retry a failed analysis.
     */
    #[Authorize('update', 'resumeAnalysis')]
    public function retry(Request $request, ResumeAnalysis $resumeAnalysis): RedirectResponse
    {
        try {
            $this->analysisService->retryAnalysis($resumeAnalysis, $request->boolean('force'));

            return redirect()->route('resume-analyses.show', $resumeAnalysis)
                ->with('success', 'Analysis queued for retry!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
