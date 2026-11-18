<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class EnrollmentPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('enrollments.view');
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        if ($enrollment->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('enrollments.view') && $this->access->canManage($user, $enrollment->course);
    }

    public function create(User $user): bool
    {
        return $user->can('enrollments.manage');
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->can('enrollments.manage') && $this->access->canManage($user, $enrollment->course);
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $this->update($user, $enrollment);
    }
}
