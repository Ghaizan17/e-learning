<?php

namespace App\Filament\Resources\Classrooms\Tables;

use App\Enums\GradeLevel;
use App\Models\AcademicYear;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->grow()
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academicYear.name')
                    ->label('Tahun ajaran')
                    ->sortable(),
                TextColumn::make('grade_level')
                    ->label('Tingkat')
                    ->badge()
                    ->formatStateUsing(fn (GradeLevel $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Program keahlian')
                    ->placeholder('Umum')
                    ->toggleable(),
                TextColumn::make('homeroomTeacher.name')
                    ->label('Wali kelas')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('active_members_count')
                    ->label('Siswa')
                    ->counts('activeMembers')
                    ->formatStateUsing(fn (int $state, $record): string => $state.' / '.$record->capacity)
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun ajaran')
                    ->options(fn (): array => AcademicYear::query()->orderByDesc('start_date')->pluck('name', 'id')->all()),
                SelectFilter::make('grade_level')
                    ->label('Tingkat')
                    ->options(collect(GradeLevel::cases())->mapWithKeys(
                        fn (GradeLevel $level): array => [$level->value => $level->label()],
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
