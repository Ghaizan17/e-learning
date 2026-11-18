<?php

namespace App\Policies;

use App\Models\TeacherAssignment;
use App\Models\User;

class TeacherAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teacher_assignments.view');
    }

    public function view(User $user, TeacherAssignment $teacherAssignment): bool
    {
        if (! $user->can('teacher_assignments.view')) {
            return false;
        }

        return $user->isAdmin()
            || $teacherAssignment->teacher_id === $user->getKey()
            || $user->isHomeroomOf($teacherAssignment->classroom);
    }

    public function create(User $user): bool
    {
        return $user->can('teacher_assignments.manage');
    }

    public function update(User $user, TeacherAssignment $teacherAssignment): bool
    {
        return $user->can('teacher_assignments.manage');
    }

    public function delete(User $user, TeacherAssignment $teacherAssignment): bool
    {
        return $user->can('teacher_assignments.manage');
    }
}
