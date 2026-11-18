<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Notifications\AssignmentDeadlineReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class SendAssignmentDeadlineReminders implements ShouldQueue
{
    use Queueable;

    /**
     * Notify students who have not submitted an assignment due within 24 hours.
     */
    public function handle(): void
    {
        $assignments = Assignment::query()
            ->published()
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->with(['course.enrollments.user'])
            ->get();

        foreach ($assignments as $assignment) {
            $submittedUserIds = $assignment->submissions()
                ->where('is_latest', true)
                ->pluck('user_id')
                ->all();

            $recipients = $assignment->course->enrollments
                ->where('status', 'active')
                ->whereNotIn('user_id', $submittedUserIds)
                ->pluck('user')
                ->filter()
                ->reject(fn ($student): bool => $this->alreadyReminded($student, $assignment));

            if ($recipients->isNotEmpty()) {
                $recipients->each->notify(new AssignmentDeadlineReminderNotification($assignment));
            }
        }
    }

    /**
     * Avoid sending the same reminder more than once every 12 hours.
     */
    private function alreadyReminded($student, Assignment $assignment): bool
    {
        /** @var Collection<int, mixed> $recent */
        return $student->notifications()
            ->where('data->type', 'assignment_deadline_reminder')
            ->where('data->assignment_id', $assignment->getKey())
            ->where('created_at', '>=', now()->subHours(12))
            ->exists();
    }
}
