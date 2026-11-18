<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentDeadlineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Assignment $assignment) {}

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
        return [
            'type' => 'assignment_deadline_reminder',
            'title' => 'Tenggat mendekat: '.$this->assignment->title,
            'body' => 'Kumpulkan sebelum '.$this->assignment->due_at->translatedFormat('d M Y H:i').'.',
            'assignment_id' => $this->assignment->getKey(),
            'course_id' => $this->assignment->course_id,
            'url' => route('student.assignments.show', $this->assignment),
        ];
    }
}
