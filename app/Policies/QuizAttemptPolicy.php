<?php

namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class QuizAttemptPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function view(User $user, QuizAttempt $attempt): bool
    {
        if ($attempt->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('quiz_attempts.view')
            && $this->access->canManage($user, $attempt->quiz->course);
    }

    public function submit(User $user, QuizAttempt $attempt): bool
    {
        return $attempt->user_id === $user->getKey()
            && $attempt->status->value === 'in_progress'
            && $user->can('quizzes.view');
    }

    public function grade(User $user, QuizAttempt $attempt): bool
    {
        return $user->can('quizzes.manage')
            && $this->access->canManage($user, $attempt->quiz->course);
    }

    public function viewResult(User $user, QuizAttempt $attempt): bool
    {
        return $this->view($user, $attempt) && $attempt->canRevealResult();
    }
}
