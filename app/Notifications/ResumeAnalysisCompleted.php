<?php

namespace App\Notifications;

use App\Models\ResumeAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResumeAnalysisCompleted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ResumeAnalysis $resumeAnalysis
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
