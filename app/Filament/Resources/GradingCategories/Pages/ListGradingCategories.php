<?php

namespace App\Filament\Resources\GradingCategories\Pages;

use App\Filament\Resources\GradingCategories\GradingCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGradingCategories extends ListRecords
{
    protected static string $resource = GradingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
