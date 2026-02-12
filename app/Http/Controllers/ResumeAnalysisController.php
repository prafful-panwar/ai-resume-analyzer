<?php

namespace App\Http\Controllers;

use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Services\ResumeAnalysisService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        private ResumeAnalysisService $analysisService
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

        $analyses = $user->resumeAnalyses()
            ->with(['jobDescription'])
            ->latest()
            ->get();

        return Inertia::render('ResumeAnalyses/Index', [
            'analyses' => $analyses,
        ]);
    }

    /**
     * Display the specified resume analysis.
     */
    public function show(ResumeAnalysis $resumeAnalysis): Response
    {
        $this->authorize('view', $resumeAnalysis);

        $resumeAnalysis->load(['jobDescription', 'logs']);

        return Inertia::render('ResumeAnalyses/Show', [
            'analysis' => $resumeAnalysis,
        ]);
    }

    /**
     * Download the resume file.
     */
    public function download(ResumeAnalysis $resumeAnalysis): BinaryFileResponse
    {
        $this->authorize('view', $resumeAnalysis);

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
    public function retry(Request $request, ResumeAnalysis $resumeAnalysis): RedirectResponse
    {
        $this->authorize('view', $resumeAnalysis);

        try {
            $this->analysisService->retryAnalysis($resumeAnalysis, $request->boolean('force'));

            return redirect()->route('resume-analyses.show', $resumeAnalysis)
                ->with('success', 'Analysis queued for retry!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
