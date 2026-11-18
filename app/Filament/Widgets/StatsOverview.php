<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Tiptap\Nodes\OrderedList;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Materials', Material::query()->count())
                ->description('All current materials in total')
                ->descriptionIcon('heroicon-o-document-text')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total User', User::query()->count())
                ->description('All current users in total')
                ->descriptionIcon('heroicon-o-users')
                ->chart([17, 15, 10, 8, 15, 4, 2])
                ->color('info'),
        ];
    }
}
