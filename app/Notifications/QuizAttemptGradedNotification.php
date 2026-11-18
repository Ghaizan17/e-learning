<?php

namespace App\Notifications;

use App\Models\QuizAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuizAttemptGradedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly QuizAttempt $attempt) {}

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
            'type' => 'quiz_graded',
            'title' => 'Kuis dinilai: '.$this->attempt->quiz?->title,
            'body' => 'Skor '.$this->attempt->score.' dari '.$this->attempt->quiz?->totalPoints(),
            'quiz_id' => $this->attempt->quiz_id,
            'attempt_id' => $this->attempt->getKey(),
            'url' => $this->attempt->quiz !== null
                ? route('student.quizzes.show', $this->attempt->quiz)
                : null,
        ];
    }
}
