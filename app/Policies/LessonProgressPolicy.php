<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class LessonProgressPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function view(User $user, LessonProgress $progress): bool
    {
        if ($progress->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('courses.manage') && $this->access->canManage($user, $progress->lesson->chapter->course);
    }

    public function update(User $user, LessonProgress $progress): bool
    {
        return $progress->user_id === $user->getKey()
            && $this->access->canAccessLesson($user, $progress->lesson);
    }

    public function create(User $user, Lesson $lesson): bool
    {
        return $this->access->canAccessLesson($user, $lesson);
    }
}
