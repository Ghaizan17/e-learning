<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Role;

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
        $totalMaterials = Material::query()->count();
        $totalRoles = Role::query()->count();

        return [
            Stat::make('Total pengguna', $totalUsers)
                ->description($activeUsers.' akun aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            Stat::make('Total materi', $totalMaterials)
                ->description('Materi pembelajaran tersimpan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            Stat::make('Peran sistem', $totalRoles)
                ->description('Peran terdaftar di Spatie Permission')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
        ];
    }
}
