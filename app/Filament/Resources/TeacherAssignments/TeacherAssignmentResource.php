<?php

namespace App\Filament\Resources\TeacherAssignments;

use App\Filament\Resources\TeacherAssignments\Pages\CreateTeacherAssignment;
use App\Filament\Resources\TeacherAssignments\Pages\EditTeacherAssignment;
use App\Filament\Resources\TeacherAssignments\Pages\ListTeacherAssignments;
use App\Filament\Resources\TeacherAssignments\Pages\ViewTeacherAssignment;
use App\Filament\Resources\TeacherAssignments\Schemas\TeacherAssignmentForm;
use App\Filament\Resources\TeacherAssignments\Schemas\TeacherAssignmentInfolist;
use App\Filament\Resources\TeacherAssignments\Tables\TeacherAssignmentsTable;
use App\Models\TeacherAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = TeacherAssignment::class;

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $modelLabel = 'penugasan mengajar';

    protected static ?string $pluralModelLabel = 'penugasan mengajar';

    public static function form(Schema $schema): Schema
    {
        return TeacherAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeacherAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeacherAssignmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeacherAssignments::route('/'),
            'create' => CreateTeacherAssignment::route('/create'),
            'view' => ViewTeacherAssignment::route('/{record}'),
            'edit' => EditTeacherAssignment::route('/{record}/edit'),
        ];
    }
}
