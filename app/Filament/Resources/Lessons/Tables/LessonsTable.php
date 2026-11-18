<?php

namespace App\Filament\Resources\Lessons\Tables;

use App\Enums\LessonType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('chapter.course.title')
                    ->label('Kursus')
                    ->searchable(),
                TextColumn::make('chapter.title')
                    ->label('Bab'),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('position')
                    ->label('#')
                    ->alignCenter(),
                IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(collect(LessonType::cases())->mapWithKeys(
                        fn (LessonType $type): array => [$type->value => $type->label()],
                    )),
                SelectFilter::make('chapter_id')
                    ->label('Bab')
                    ->relationship('chapter', 'title')
                    ->searchable()
                    ->preload(),
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
