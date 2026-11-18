<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class CoursePolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('courses.view');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can('courses.view') && $this->access->canView($user, $course);
    }

    public function create(User $user): bool
    {
        return $user->can('courses.manage');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('courses.manage') && $this->access->canManage($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('courses.manage') && $this->access->canManage($user, $course);
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->can('courses.publish') && $this->access->canManage($user, $course);
    }

    public function manageEnrollments(User $user, Course $course): bool
    {
        return $user->can('enrollments.manage') && $this->access->canManage($user, $course);
    }
}
