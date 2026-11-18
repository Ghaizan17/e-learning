<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('creates every role and permission exactly once when seeded twice', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect(Role::count())->toBe(count(RoleName::cases()))
        ->and(Permission::count())->toBe(count(RolePermissionSeeder::permissions()))
        ->and(Role::where('name', RoleName::SuperAdmin->value)->first()->permissions)->toHaveCount(count(RolePermissionSeeder::permissions()));
});

it('does not duplicate demo accounts when seeded twice', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    expect(User::count())->toBe(5)
        ->and(User::where('email', 'superadmin@school.test')->first()->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and(User::where('email', 'siswa@school.test')->first()->hasRole(RoleName::Student->value))->toBeTrue();
});

it('grants the school admin every permission except role and settings management', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $role = Role::where('name', RoleName::SchoolAdmin->value)->first();

    expect($role->hasPermissionTo('users.view'))->toBeTrue()
        ->and($role->hasPermissionTo('roles.manage'))->toBeFalse()
        ->and($role->hasPermissionTo('settings.manage'))->toBeFalse();
});

it('keeps students out of management permissions', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $role = Role::where('name', RoleName::Student->value)->first();

    expect($role->hasPermissionTo('courses.view'))->toBeTrue()
        ->and($role->hasPermissionTo('courses.manage'))->toBeFalse()
        ->and($role->hasPermissionTo('grades.manage'))->toBeFalse();
});
