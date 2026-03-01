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

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $jobDescription = JobDescription::factory()->create([
        'user_id' => $this->user->id,
        'job_role' => 'Software Engineer',
    ]);

    $this->analysis = ResumeAnalysis::factory()->create([
        'user_id' => $this->user->id,
        'job_description_id' => $jobDescription->id,
        'status' => 'completed',
        'result' => ['match_score' => 85],
    ]);

    $this->notification = new ResumeAnalysisCompleted($this->analysis);
});

test('notification is sent via database and broadcast channels when no slack webhook configured', function (): void {
    config(['services.slack.notifications_webhook_url' => null]);

    $channels = $this->notification->via($this->user);

    expect($channels)->toContain('database')
        ->and($channels)->toContain('broadcast')
        ->and($channels)->not->toContain('slack');
});

test('notification includes slack channel when webhook is configured', function (): void {
    config(['services.slack.notifications_webhook_url' => 'https://hooks.slack.com/services/test']);

    $channels = $this->notification->via($this->user);

    expect($channels)->toContain('slack');
});

test('toArray payload contains required keys', function (): void {
    $data = $this->notification->toArray($this->user);

    expect($data)
        ->toHaveKeys(['analysis_id', 'job_role', 'status', 'message', 'match_score'])
        ->and($data['analysis_id'])->toBe($this->analysis->id)
        ->and($data['job_role'])->toBe('Software Engineer')
        ->and($data['status'])->toBe('completed')
        ->and($data['match_score'])->toBe(85);
});

test('toBroadcast returns a BroadcastMessage with the correct payload', function (): void {
    $broadcast = $this->notification->toBroadcast($this->user);

    expect($broadcast)->toBeInstanceOf(BroadcastMessage::class)
        ->and($broadcast->data['analysis_id'])->toBe($this->analysis->id)
        ->and($broadcast->data['message'])->toContain('Software Engineer');
});

test('toSlack returns a SlackMessage instance', function (): void {
    $slack = $this->notification->toSlack($this->user);

    expect($slack)->toBeInstanceOf(SlackMessage::class);
});

test('failed analysis payload contains error and omits match score', function (): void {
    $this->analysis->update(['status' => 'failed', 'error_message' => 'AI timeout']);
    $this->analysis->refresh();

    $notification = new ResumeAnalysisCompleted($this->analysis);
    $data = $notification->toArray($this->user);

    expect($data)
        ->toHaveKey('error')
        ->not->toHaveKey('match_score')
        ->and($data['message'])->toContain('failed');
});

test('notification is sent to the user when the job completes', function (): void {
    Notification::fake();

    $this->user->notify(new ResumeAnalysisCompleted($this->analysis));

    Notification::assertSentTo(
        $this->user,
        ResumeAnalysisCompleted::class,
        function (ResumeAnalysisCompleted $notification): bool {
            $data = $notification->toArray($this->user);

            return $data['analysis_id'] === $this->analysis->id;
        }
    );
});
