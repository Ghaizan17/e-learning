<?php

namespace App\Enums;

/**
 * Canonical role names stored by spatie/laravel-permission.
 */
enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case SchoolAdmin = 'school_admin';
    case Teacher = 'teacher';
    case HomeroomTeacher = 'homeroom_teacher';
    case Student = 'student';

    /**
     * Human readable label used across Filament and Blade views.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::SchoolAdmin => 'Admin Sekolah',
            self::Teacher => 'Guru',
            self::HomeroomTeacher => 'Wali Kelas',
            self::Student => 'Siswa',
        };
    }

    /**
     * Roles allowed to open the Filament admin panel.
     *
     * @return array<int, string>
     */
    public static function adminValues(): array
    {
        return [self::SuperAdmin->value, self::SchoolAdmin->value];
    }

    /**
     * Roles allowed to open the teacher portal.
     *
     * @return array<int, string>
     */
    public static function teacherValues(): array
    {
        return [self::Teacher->value, self::HomeroomTeacher->value];
    }

    /**
     * All role names in display order.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role): string => $role->value, self::cases());
    }
}
