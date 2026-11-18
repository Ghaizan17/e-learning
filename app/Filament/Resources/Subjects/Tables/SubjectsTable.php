<?php

namespace App\Filament\Resources\Subjects\Tables;

use App\Models\Department;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Program keahlian')
                    ->placeholder('Umum')
                    ->sortable(),
                TextColumn::make('teacher_assignments_count')
                    ->label('Penugasan')
                    ->counts('teacherAssignments')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Program keahlian')
                    ->options(fn (): array => Department::query()->orderBy('name')->pluck('name', 'id')->all()),
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
