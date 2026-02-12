<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobDescriptionRequest;
use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobDescriptionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        $jobDescriptions = $user->jobDescriptions()
            ->orderBy('created_at', 'desc')
            ->get();

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

        $user->jobDescriptions()->create($request->validated());

        return redirect()->route('job-descriptions.index')
            ->with('success', 'Job description created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobDescription $jobDescription): Response
    {
        $this->authorize('update', $jobDescription);

        return Inertia::render('JobDescriptions/Form', [
            'jobDescription' => $jobDescription,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreJobDescriptionRequest $request, JobDescription $jobDescription): RedirectResponse
    {
        $this->authorize('update', $jobDescription);

        $jobDescription->update($request->validated());

        return redirect()->route('job-descriptions.index')
            ->with('success', 'Job description updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobDescription $jobDescription): RedirectResponse
    {
        $this->authorize('delete', $jobDescription);

        $jobDescription->delete();

        return redirect()->route('job-descriptions.index')
            ->with('success', 'Job description deleted successfully');
    }
}
