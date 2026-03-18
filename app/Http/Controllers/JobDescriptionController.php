<?php

namespace App\Http\Controllers;

use App\DTO\JobDescriptionData;
use App\Http\Requests\StoreJobDescriptionRequest;
use App\Models\JobDescription;
use App\Models\User;
use App\Repositories\Contracts\JobDescriptionRepositoryInterface;
use App\Services\JobDescriptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

class JobDescriptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private JobDescriptionRepositoryInterface $jobDescriptionRepository,
        private JobDescriptionService $jobDescriptionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $jobDescriptions = $this->jobDescriptionRepository->getForUser($user);

        return Inertia::render('JobDescriptions/Index', [
            'jobDescriptions' => $jobDescriptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('JobDescriptions/Form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobDescriptionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = JobDescriptionData::fromArray($request->validated());
        $this->jobDescriptionService->createJobDescription($user, $dto);

        return redirect()->route('job-descriptions.index')
            ->with('success', 'Job description created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    #[Authorize('update', 'job_description')]
    public function edit(JobDescription $jobDescription): Response
    {
        return Inertia::render('JobDescriptions/Form', [
            'jobDescription' => $jobDescription,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'job_description')]
    public function update(StoreJobDescriptionRequest $request, JobDescription $jobDescription): RedirectResponse
    {
        $dto = JobDescriptionData::fromArray($request->validated());
        $updated = $this->jobDescriptionService->updateJobDescription($jobDescription, $dto);

        if (! $updated) {
            return redirect()->back()
                ->with('error', 'Failed to update job description. Please try again.');
        }

        return redirect()->route('job-descriptions.index')
            ->with('success', 'Job description updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'job_description')]
    public function destroy(JobDescription $jobDescription): RedirectResponse
    {
        $deleted = $this->jobDescriptionService->deleteJobDescription($jobDescription);

        if (! $deleted) {
            return redirect()->back()
                ->with('error', 'Failed to delete job description. Please try again.');
        }

        return redirect()->route('job-descriptions.index')
            ->with('success', 'Job description deleted successfully.');
    }
}
