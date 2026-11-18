<?php

namespace App\Filament\Widgets;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * Real aggregate counters taken from the database.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('is_active', true)->count();
        $totalClasses = Classroom::query()->count();
        $publishedCourses = Course::query()->published()->count();
        $totalEnrollments = Enrollment::query()->count();

        return [
            Stat::make('Pengguna', $totalUsers)
                ->description($activeUsers.' akun aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            Stat::make('Kelas', $totalClasses)
                ->description('Kelas pada semua tahun ajaran')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Kursus terbit', $publishedCourses)
                ->description('Dari '.Course::query()->count().' kursus')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
            Stat::make('Pendaftaran kursus', $totalEnrollments)
                ->description('Siswa terdaftar di kursus')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
        ];
    }
}
