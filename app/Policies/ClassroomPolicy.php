<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    /**
     * Administrative listing is available to anyone with the view permission.
     * Teacher/homeroom rows are narrowed further in the queries themselves.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('classrooms.view');
    }

    public function view(User $user, Classroom $classroom): bool
    {
        if (! $user->can('classrooms.view')) {
            return false;
        }

        return $user->isAdmin()
            || $user->teachesIn($classroom)
            || $user->isHomeroomOf($classroom);
    }

    public function create(User $user): bool
    {
        return $user->can('classrooms.manage');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.manage');
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $user->can('classrooms.manage');
    }

    /**
     * Manage the student roster of a classroom.
     */
    public function manageMembers(User $user, Classroom $classroom): bool
    {
        return $user->can('classroom_members.manage');
    }
}
