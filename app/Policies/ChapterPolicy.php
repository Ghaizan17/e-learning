<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class ChapterPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function view(User $user, Chapter $chapter): bool
    {
        return $user->can('courses.view') && $this->access->canView($user, $chapter->course);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->can('chapters.manage') && $this->access->canManage($user, $course);
    }

    public function update(User $user, Chapter $chapter): bool
    {
        return $user->can('chapters.manage') && $this->access->canManage($user, $chapter->course);
    }

    public function delete(User $user, Chapter $chapter): bool
    {
        return $this->update($user, $chapter);
    }
}
