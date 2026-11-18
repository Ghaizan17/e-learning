<?php

namespace App\Filament\Widgets;

use App\Enums\GradeLevel;
use App\Models\Classroom;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ClassroomOverview extends TableWidget
{
    protected int|string|array $columnSpan = 3;

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Kelas aktif')
            ->description('Jumlah siswa dan mata pelajaran yang diampu tiap kelas.')
            ->query(fn (): Builder => Classroom::query()
                ->with(['academicYear', 'homeroomTeacher'])
                ->withCount(['activeMembers', 'teacherAssignments'])
                ->orderBy('name'))
            ->columns([
                TextColumn::make('name')
                    ->label('Kelas')
                    ->description(fn (Classroom $record): string => $record->academicYear?->name ?? '-')
                    ->weight('semibold')
                    ->grow(),
                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->badge()
                    ->formatStateUsing(fn (GradeLevel $state): string => $state->label())
                    ->color('gray'),
                TextColumn::make('homeroomTeacher.name')
                    ->label('Wali kelas')
                    ->placeholder('Belum ditentukan')
                    ->wrap(),
                TextColumn::make('active_members_count')
                    ->label('Siswa')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('teacher_assignments_count')
                    ->label('Penugasan')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada kelas');
    }
}
