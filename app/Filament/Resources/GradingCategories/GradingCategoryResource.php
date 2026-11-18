<?php

namespace App\Filament\Resources\GradingCategories;

use App\Filament\Resources\GradingCategories\Pages\CreateGradingCategory;
use App\Filament\Resources\GradingCategories\Pages\EditGradingCategory;
use App\Filament\Resources\GradingCategories\Pages\ListGradingCategories;
use App\Filament\Resources\GradingCategories\Schemas\GradingCategoryForm;
use App\Filament\Resources\GradingCategories\Tables\GradingCategoriesTable;
use App\Models\GradingCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GradingCategoryResource extends Resource
{
    protected static ?string $model = GradingCategory::class;

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $modelLabel = 'kategori nilai';

    protected static ?string $pluralModelLabel = 'kategori nilai';

    public static function form(Schema $schema): Schema
    {
        return GradingCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradingCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGradingCategories::route('/'),
            'create' => CreateGradingCategory::route('/create'),
            'edit' => EditGradingCategory::route('/{record}/edit'),
        ];
    }
}
