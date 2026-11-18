<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class LessonPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function view(User $user, Lesson $lesson): bool
    {
        return $user->can('courses.view') && $this->access->canAccessLesson($user, $lesson);
    }

    public function create(User $user, ?Chapter $chapter = null): bool
    {
        if ($chapter === null) {
            return $user->can('lessons.manage');
        }

        return $user->can('lessons.manage') && $this->access->canManage($user, $chapter->course);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('lessons.manage') && $this->access->canManage($user, $lesson->chapter->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}
