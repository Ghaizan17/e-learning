<?php

namespace App\Policies;

use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    public function view(User $user, StudentProfile $studentProfile): bool
    {
        if (! $user->can('students.view')) {
            return false;
        }

        return $user->isAdmin() || $studentProfile->user_id === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->can('students.manage');
    }

    public function update(User $user, StudentProfile $studentProfile): bool
    {
        return $user->can('students.manage');
    }

    public function delete(User $user, StudentProfile $studentProfile): bool
    {
        return $user->can('students.manage');
    }
}
