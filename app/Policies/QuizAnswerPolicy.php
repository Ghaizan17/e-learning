<?php

namespace App\Policies;

use App\Models\QuizAnswer;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class QuizAnswerPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function grade(User $user, QuizAnswer $answer): bool
    {
        return $user->can('quizzes.manage')
            && $this->access->canManage($user, $answer->attempt->quiz->course);
    }
}
