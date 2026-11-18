<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentGradedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Submission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $assignment = $this->submission->assignment;

        return [
            'type' => 'assignment_graded',
            'title' => 'Tugas dinilai: '.$assignment?->title,
            'body' => 'Skor '.$this->submission->grade?->score.' / '.$assignment?->max_score,
            'assignment_id' => $assignment?->getKey(),
            'submission_id' => $this->submission->getKey(),
            'url' => $assignment !== null ? route('student.assignments.show', $assignment) : null,
        ];
    }
}
