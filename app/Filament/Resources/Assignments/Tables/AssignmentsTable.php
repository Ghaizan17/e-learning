<?php

namespace App\Filament\Resources\Assignments\Tables;

use App\Models\Assignment;
use App\Services\Learning\AssignmentService;
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

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->grow()
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course.title')
                    ->label('Kursus')
                    ->searchable(),
                TextColumn::make('course.teacherAssignment.classroom.name')
                    ->label('Kelas'),
                TextColumn::make('due_at')
                    ->label('Tenggat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('submissions_count')
                    ->label('Pengumpulan')
                    ->counts('submissions')
                    ->alignCenter(),
                IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean(),
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
                    ->visible(fn (Assignment $record): bool => ! $record->is_published)
                    ->action(function (Assignment $record): void {
                        try {
                            app(AssignmentService::class)->publish($record);
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Tugas belum bisa diterbitkan')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Tugas diterbitkan')->success()->send();
                    }),
                Action::make('unpublish')
                    ->label('Sembunyikan')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Assignment $record): bool => $record->is_published)
                    ->action(function (Assignment $record): void {
                        app(AssignmentService::class)->unpublish($record);

                        Notification::make()->title('Tugas disembunyikan')->success()->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_at', 'desc');
    }
}
