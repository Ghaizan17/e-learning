<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class QuizPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('quizzes.view');
    }

    public function view(User $user, Quiz $quiz): bool
    {
        $course = $quiz->course;

        if ($course === null || ! $this->access->canView($user, $course)) {
            return false;
        }

        return $quiz->is_published || $this->access->canManage($user, $course);
    }

    public function create(User $user, ?Course $course = null): bool
    {
        if ($course === null) {
            return $user->can('quizzes.manage');
        }

        return $user->can('quizzes.manage') && $this->access->canManage($user, $course);
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->can('quizzes.manage') && $this->access->canManage($user, $quiz->course);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    public function publish(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    /**
     * Whether the student may start an attempt (window and attempt limits are enforced in the service).
     */
    public function take(User $user, Quiz $quiz): bool
    {
        return $user->can('quizzes.view')
            && $quiz->is_published
            && $this->access->isEnrolled($user, $quiz->course);
    }

    public function gradeAttempts(User $user, Quiz $quiz): bool
    {
        return $user->can('quizzes.manage') && $this->access->canManage($user, $quiz->course);
    }
}
