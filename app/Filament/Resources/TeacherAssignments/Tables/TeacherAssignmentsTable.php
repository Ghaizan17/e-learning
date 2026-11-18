<?php

namespace App\Filament\Resources\TeacherAssignments\Tables;

use App\Enums\SemesterName;
use App\Models\AcademicYear;
use App\Models\Semester;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeacherAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable(),
                TextColumn::make('subject.name')
                    ->label('Mata pelajaran')
                    ->searchable(),
                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->searchable(),
                TextColumn::make('semester.name')
                    ->label('Semester')
                    ->badge()
                    ->formatStateUsing(fn (SemesterName $state): string => $state->label()),
                TextColumn::make('academicYear.name')
                    ->label('Tahun ajaran')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun ajaran')
                    ->options(fn (): array => AcademicYear::query()->orderByDesc('start_date')->pluck('name', 'id')->all()),
                SelectFilter::make('semester_id')
                    ->label('Semester')
                    ->options(fn (): array => Semester::query()
                        ->with('academicYear')
                        ->orderByDesc('start_date')
                        ->get()
                        ->mapWithKeys(fn (Semester $semester): array => [
                            $semester->getKey() => ($semester->academicYear?->name ?? '-').' - '.$semester->name->label(),
                        ])
                        ->all()),
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
            ->defaultSort('created_at', 'desc');
    }
}
