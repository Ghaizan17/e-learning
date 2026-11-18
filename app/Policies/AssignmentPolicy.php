<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class AssignmentPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('courses.view');
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if (! $user->can('courses.view')) {
            return false;
        }

        $course = $assignment->course;

        if ($course === null || ! $this->access->canView($user, $course)) {
            return false;
        }

        return $assignment->is_published || $this->access->canManage($user, $course);
    }

    public function create(User $user, Course $course): bool
    {
        return $user->can('courses.manage') && $this->access->canManage($user, $course);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->can('courses.manage') && $this->access->canManage($user, $assignment->course);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function publish(User $user, Assignment $assignment): bool
    {
        return $user->can('courses.publish') && $this->access->canManage($user, $assignment->course);
    }

    public function gradeSubmissions(User $user, Assignment $assignment): bool
    {
        return $user->can('submissions.grade') && $this->access->canManage($user, $assignment->course);
    }
}
