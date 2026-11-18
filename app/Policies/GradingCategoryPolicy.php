<?php

namespace App\Policies;

use App\Models\GradingCategory;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class GradingCategoryPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('grading_scheme.manage');
    }

    public function view(User $user, GradingCategory $category): bool
    {
        return $user->can('grading_scheme.manage') && $this->access->canManage($user, $category->course);
    }

    public function create(User $user): bool
    {
        return $user->can('grading_scheme.manage');
    }

    public function update(User $user, GradingCategory $category): bool
    {
        return $this->view($user, $category);
    }

    public function delete(User $user, GradingCategory $category): bool
    {
        return $this->view($user, $category);
    }
}
