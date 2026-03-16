<?php

use App\Jobs\AnalyzeResumeJob;
use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Repositories\Contracts\ResumeAnalysisRepositoryInterface;
use App\Services\ResumeAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('successfully calls service to analyze a resume', function (): void {
    $user = User::factory()->create();
    $jobDescription = JobDescription::factory()->create(['user_id' => $user->id]);
    $analysis = ResumeAnalysis::factory()->create([
        'user_id' => $user->id,
        'job_description_id' => $jobDescription->id,
    ]);

    $mockService = mock(ResumeAnalysisService::class, function ($mock): void {
        $mock->shouldReceive('performAnalysis')->once()->andReturnUsing(fn ($a) => $a);
    });

    $job = new AnalyzeResumeJob($analysis);

    /** @var ResumeAnalysisService&MockInterface $mockService */
    $job->handle($mockService);
});

it('handles retries and final failure logging without a user gracefully', function (): void {
    Log::shouldReceive('error')->once();

    $analysis = ResumeAnalysis::factory()->make([
        'id' => 1,
        'user_id' => 999999, // user doesn't exist
    ]);

    $job = new AnalyzeResumeJob($analysis);

    // Test the uniqueId and tags methods for coverage
    expect($job->uniqueId())->toBe((string) $analysis->id)
        ->and($job->tags())->toContain('resume-analysis', 'user:999999', 'analysis:'.$analysis->id);

    // Mock Repository to test logFailure execution
    $repoMock = mock(ResumeAnalysisRepositoryInterface::class, function ($mock): void {
        $mock->shouldReceive('markAsFailed')->once()->andReturn(true);
    });
    app()->instance(ResumeAnalysisRepositoryInterface::class, $repoMock);

    // Call failed artificially to test logging branch
    $exception = new Exception('Failed gracefully');
    $job->failed($exception);
});
