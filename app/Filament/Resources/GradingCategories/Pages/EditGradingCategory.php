<?php

namespace App\Filament\Resources\GradingCategories\Pages;

use App\Filament\Resources\GradingCategories\GradingCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGradingCategory extends EditRecord
{
    protected static string $resource = GradingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
