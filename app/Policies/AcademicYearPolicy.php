<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('academic_years.view');
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic_years.view');
    }

    public function create(User $user): bool
    {
        return $user->can('academic_years.manage');
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic_years.manage');
    }

    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->can('academic_years.manage');
    }
}
