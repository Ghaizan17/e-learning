<?php

namespace App\Filament\Resources\Grades;

use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Filament\Resources\Grades\Pages\ViewGrade;
use App\Filament\Resources\Grades\RelationManagers\HistoriesRelationManager;
use App\Filament\Resources\Grades\Schemas\GradeInfolist;
use App\Filament\Resources\Grades\Tables\GradesTable;
use App\Models\Grade;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $modelLabel = 'nilai';

    protected static ?string $pluralModelLabel = 'nilai';

    public static function infolist(Schema $schema): Schema
    {
        return GradeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GradesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [HistoriesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGrades::route('/'),
            'view' => ViewGrade::route('/{record}'),
        ];
    }
}
