<?php

namespace App\Filament\Widgets;

use App\Enums\RoleName;
use Filament\Widgets\ChartWidget;
use Spatie\Permission\Models\Role;

class RoleDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Sebaran pengguna';

    protected ?string $description = 'Jumlah akun per peran.';

    protected ?string $maxHeight = '240px';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 5;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $options = [
        'plugins' => [
            'legend' => ['position' => 'bottom'],
        ],
    ];

    protected function getData(): array
    {
        $roles = Role::query()->withCount('users')->orderBy('name')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pengguna',
                    'data' => $roles->pluck('users_count')->all(),
                    'backgroundColor' => [
                        '#6366f1', '#10b981', '#f59e0b', '#0ea5e9', '#ec4899', '#8b5cf6',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $roles->map(fn (Role $role): string => RoleName::tryFrom($role->name)?->label() ?? $role->name)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
