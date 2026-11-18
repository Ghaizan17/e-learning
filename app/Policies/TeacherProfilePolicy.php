<?php

namespace App\Policies;

use App\Models\TeacherProfile;
use App\Models\User;

class TeacherProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    public function view(User $user, TeacherProfile $teacherProfile): bool
    {
        if (! $user->can('students.view')) {
            return false;
        }

        return $user->isAdmin() || $teacherProfile->user_id === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->can('students.manage');
    }

    public function update(User $user, TeacherProfile $teacherProfile): bool
    {
        return $user->can('students.manage');
    }

    public function delete(User $user, TeacherProfile $teacherProfile): bool
    {
        return $user->can('students.manage');
    }
}
