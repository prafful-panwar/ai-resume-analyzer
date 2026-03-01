<?php

use App\Models\JobDescription;
use App\Models\ResumeAnalysis;
use App\Models\User;
use App\Notifications\ResumeAnalysisCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/**
 * @return array{User, ResumeAnalysis, ResumeAnalysisCompleted}
 */
function setupTestData(): array
{
    $user = User::factory()->create();

    $jobDescription = JobDescription::factory()->create([
        'user_id' => $user->id,
        'job_role' => 'Software Engineer',
    ]);

    $analysis = ResumeAnalysis::factory()->create([
        'user_id' => $user->id,
        'job_description_id' => $jobDescription->id,
        'status' => 'completed',
        'result' => ['match_score' => 85],
    ]);

    $notification = new ResumeAnalysisCompleted($analysis);

    return [$user, $analysis, $notification];
}

test('notification is sent via database and broadcast channels when no slack webhook configured', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    config(['services.slack.notifications_webhook_url' => null]);

    $channels = $notification->via($user);

    expect($channels)->toContain('database')
        ->and($channels)->toContain('broadcast')
        ->and($channels)->not->toContain('slack');
});

test('notification includes slack channel when webhook is configured', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    config(['services.slack.notifications_webhook_url' => 'https://hooks.slack.com/services/test']);

    $channels = $notification->via($user);

    expect($channels)->toContain('slack');
});

test('toArray payload contains required keys', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    $data = $notification->toArray($user);

    expect($data)
        ->toHaveKeys(['analysis_id', 'job_role', 'status', 'message', 'match_score'])
        ->and($data['analysis_id'])->toBe($analysis->id)
        ->and($data['job_role'])->toBe('Software Engineer')
        ->and($data['status'])->toBe('completed')
        ->and($data['match_score'])->toBe(85);
});

test('toBroadcast returns a BroadcastMessage with the correct payload', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    $broadcast = $notification->toBroadcast($user);

    expect($broadcast)->toBeInstanceOf(BroadcastMessage::class)
        ->and($broadcast->data['analysis_id'])->toBe($analysis->id)
        ->and($broadcast->data['message'])->toContain('Software Engineer');
});

test('toSlack returns a SlackMessage instance', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    $slack = $notification->toSlack($user);

    expect($slack)->toBeInstanceOf(SlackMessage::class);
});

test('failed analysis payload contains error and omits match score', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    $analysis->update(['status' => 'failed', 'error_message' => 'AI timeout']);
    $analysis->refresh();

    $notification = new ResumeAnalysisCompleted($analysis);
    $data = $notification->toArray($user);

    expect($data)
        ->toHaveKey('error')
        ->and($data['message'])->toContain('failed');

    expect($data)->not->toHaveKey('match_score');
});

test('notification is sent to the user when the job completes', function (): void {
    [$user, $analysis, $notification] = setupTestData();
    Notification::fake();

    $user->notify(new ResumeAnalysisCompleted($analysis));

    Notification::assertSentTo(
        $user,
        ResumeAnalysisCompleted::class,
        function (ResumeAnalysisCompleted $notification) use ($user, $analysis): bool {
            $data = $notification->toArray($user);

            return $data['analysis_id'] === $analysis->id;
        }
    );
});
