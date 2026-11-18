<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Use three columns so dashboard widgets sit next to each other instead
     * of leaving half-empty rows on wide screens.
     */
    public function getColumns(): int
    {
        return 3;
    }
}
