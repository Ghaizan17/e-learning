<?php

namespace App\Filament\Resources\GradingCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GradingCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.title')->label('Kursus')->searchable()->sortable(),
                TextColumn::make('name')->label('Kategori')->badge(),
                TextColumn::make('weight')->label('Bobot')->suffix('%')->alignCenter()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('course_id');
    }
}
