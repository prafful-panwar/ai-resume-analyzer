<?php

namespace App\Notifications;

use App\Models\ResumeAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

#[DeleteWhenMissingModels]
class ResumeAnalysisCompleted extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ResumeAnalysis $resumeAnalysis
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (config('services.slack.notifications_webhook_url')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $jobDescription = $this->resumeAnalysis->jobDescription;
        $jobRole = $jobDescription ? $jobDescription->job_role : 'Unknown Role';
        $isCompleted = $this->resumeAnalysis->isCompleted();

        /** @var array<string, mixed> $result */
        $result = (array) $this->resumeAnalysis->result;
        $matchScore = $isCompleted ? ($result['match_score'] ?? null) : null;

        return (new SlackMessage)
            ->from('Resume Analyzer', ':page_facing_up:')
            ->attachment(function (SlackAttachment $attachment) use ($jobRole, $isCompleted, $matchScore): void {
                $fields = [
                    'Job Role' => $jobRole,
                    'Status' => Str::ucfirst($this->resumeAnalysis->status),
                ];

                if ($matchScore !== null) {
                    $fields['Match Score'] = Number::percentage($matchScore, maxPrecision: 0);
                }

                if (! $isCompleted) {
                    $fields['Error'] = (string) $this->resumeAnalysis->error_message;
                }

                $attachment
                    ->title($isCompleted ? '✅ Resume Analysis Complete' : '❌ Resume Analysis Failed')
                    ->color($isCompleted ? 'good' : 'danger')
                    ->fields($fields)
                    ->footer('Resume Analyzer')
                    ->timestamp(now());
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $jobDescription = $this->resumeAnalysis->jobDescription;
        $jobRole = $jobDescription ? $jobDescription->job_role : 'Unknown Role';

        $data = [
            'analysis_id' => $this->resumeAnalysis->id,
            'job_role' => $jobRole,
            'status' => $this->resumeAnalysis->status,
        ];

        if ($this->resumeAnalysis->isCompleted()) {
            /** @var array<string, mixed> $result */
            $result = (array) $this->resumeAnalysis->result;
            $data['match_score'] = $result['match_score'] ?? null;
            $data['message'] = "Resume analysis for {$jobRole} is complete!";
        } else {
            $data['message'] = "Resume analysis for {$jobRole} failed.";
            $data['error'] = (string) $this->resumeAnalysis->error_message;
        }

        return $data;
    }
}
