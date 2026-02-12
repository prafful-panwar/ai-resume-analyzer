<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobDescriptionRequest;
use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobDescriptionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's job descriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $jobDescriptions = $user->jobDescriptions()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobDescriptions,
        ]);
    }

    /**
     * Store a newly created job description.
     */
    public function store(StoreJobDescriptionRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $jobDescription = $user->jobDescriptions()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Job description created successfully',
            'data' => $jobDescription,
        ], 201);
    }

    /**
     * Display the specified job description.
     */
    public function show(JobDescription $jobDescription): JsonResponse
    {
        $this->authorize('view', $jobDescription);

        return response()->json([
            'success' => true,
            'data' => $jobDescription,
        ]);
    }

    /**
     * Update the specified job description.
     */
    public function update(StoreJobDescriptionRequest $request, JobDescription $jobDescription): JsonResponse
    {
        $this->authorize('update', $jobDescription);

        $jobDescription->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Job description updated successfully',
            'data' => $jobDescription,
        ]);
    }

    /**
     * Remove the specified job description.
     */
    public function destroy(JobDescription $jobDescription): JsonResponse
    {
        $this->authorize('delete', $jobDescription);

        $jobDescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job description deleted successfully',
        ]);
    }
}
