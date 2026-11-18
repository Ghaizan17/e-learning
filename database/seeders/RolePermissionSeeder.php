<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions grouped per module, assigned to roles below.
     *
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.manage',
            'academic_years.view', 'academic_years.manage',
            'semesters.view', 'semesters.manage',
            'departments.view', 'departments.manage',
            'classrooms.view', 'classrooms.manage',
            'subjects.view', 'subjects.manage',
            'teacher_assignments.view', 'teacher_assignments.manage',
            'students.view', 'students.manage',
            'classroom_members.manage',
            'courses.view', 'courses.manage', 'courses.publish',
            'chapters.manage', 'lessons.manage',
            'enrollments.manage',
            'assignments.view', 'assignments.manage',
            'submissions.view', 'submissions.grade',
            'quizzes.view', 'quizzes.manage', 'quiz_attempts.view',
            'grading_scheme.manage',
            'grades.view', 'grades.manage',
            'class_sessions.manage',
            'attendance.view', 'attendance.record',
            'announcements.view', 'announcements.manage',
            'discussions.view', 'discussions.manage',
            'reports.view', 'settings.manage', 'gamification.manage',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $names = self::permissions();
        $existing = Permission::query()->whereIn('name', $names)->pluck('name')->all();
        $now = now();

        $rows = collect(array_diff($names, $existing))
            ->map(fn (string $name): array => [
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            Permission::query()->insert($rows);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $teacherPermissions = [
            'academic_years.view', 'semesters.view', 'departments.view', 'subjects.view',
            'classrooms.view', 'students.view', 'teacher_assignments.view',
            'courses.view', 'courses.manage', 'courses.publish',
            'chapters.manage', 'lessons.manage',
            'assignments.view', 'assignments.manage', 'submissions.view', 'submissions.grade',
            'quizzes.view', 'quizzes.manage', 'quiz_attempts.view',
            'grading_scheme.manage', 'grades.view', 'grades.manage',
            'class_sessions.manage', 'attendance.view', 'attendance.record',
            'announcements.view', 'announcements.manage',
            'discussions.view', 'discussions.manage',
            'reports.view',
        ];

        $studentPermissions = [
            'courses.view', 'assignments.view', 'quizzes.view',
            'grades.view', 'attendance.view',
            'announcements.view', 'discussions.view',
        ];

        $assignments = [
            RoleName::SuperAdmin->value => self::permissions(),
            RoleName::SchoolAdmin->value => array_values(array_diff(
                self::permissions(),
                ['roles.manage', 'settings.manage'],
            )),
            RoleName::Teacher->value => $teacherPermissions,
            RoleName::HomeroomTeacher->value => $teacherPermissions,
            RoleName::Student->value => $studentPermissions,
        ];

        foreach ($assignments as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
