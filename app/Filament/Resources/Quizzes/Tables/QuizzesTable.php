<?php

namespace App\Filament\Resources\Quizzes\Tables;

use App\Models\Quiz;
use App\Services\Learning\QuizService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class QuizzesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->sortable(),
                TextColumn::make('course.title')->label('Kursus')->searchable(),
                TextColumn::make('course.teacherAssignment.classroom.name')->label('Kelas'),
                TextColumn::make('type')->label('Jenis')->badge(),
                TextColumn::make('questions_count')->label('Soal')->counts('questions')->alignCenter(),
                TextColumn::make('attempts_count')->label('Percobaan')->counts('attempts')->alignCenter(),
                TextColumn::make('duration_minutes')->label('Menit')->alignCenter(),
                IconColumn::make('is_published')->label('Terbit')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label('Terbit'),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Quiz $record): bool => ! $record->is_published)
                    ->action(function (Quiz $record): void {
                        try {
                            app(QuizService::class)->publish($record);
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Kuis belum bisa diterbitkan')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Kuis diterbitkan')->success()->send();
                    }),
                Action::make('unpublish')
                    ->label('Sembunyikan')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Quiz $record): bool => $record->is_published)
                    ->action(function (Quiz $record): void {
                        app(QuizService::class)->unpublish($record);

                        Notification::make()->title('Kuis disembunyikan')->success()->send();
                    }),
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
