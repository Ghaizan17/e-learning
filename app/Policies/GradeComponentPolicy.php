<?php

namespace App\Policies;

use App\Models\GradeComponent;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class GradeComponentPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('grades.view');
    }

    public function view(User $user, GradeComponent $component): bool
    {
        return $user->can('grades.view') && $this->access->canView($user, $component->course);
    }
}
