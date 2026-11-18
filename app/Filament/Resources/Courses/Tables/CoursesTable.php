<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Enums\CourseStatus;
use App\Services\Learning\CourseService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->grow()
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teacherAssignment.teacher.name')
                    ->label('Guru')
                    ->searchable(),
                TextColumn::make('teacherAssignment.classroom.name')
                    ->label('Kelas')
                    ->searchable(),
                TextColumn::make('teacherAssignment.subject.name')
                    ->label('Mata pelajaran'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('chapters_count')
                    ->label('Bab')
                    ->counts('chapters')
                    ->alignCenter(),
                TextColumn::make('enrollments_count')
                    ->label('Siswa')
                    ->counts('enrollments')
                    ->alignCenter(),
                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(CourseStatus::cases())->mapWithKeys(
                        fn (CourseStatus $status): array => [$status->value => $status->label()],
                    )),
                SelectFilter::make('teacher_assignment_id')
                    ->label('Penugasan')
                    ->relationship('teacherAssignment', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => implode(' · ', array_filter([
                        $record->classroom?->name,
                        $record->subject?->name,
                    ])))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('Terbitkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status !== CourseStatus::Published)
                    ->action(function ($record): void {
                        try {
                            app(CourseService::class)->publish($record);
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Kursus belum bisa diterbitkan')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Kursus diterbitkan')->success()->send();
                    }),
                Action::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === CourseStatus::Published)
                    ->action(function ($record): void {
                        app(CourseService::class)->archive($record);

                        Notification::make()->title('Kursus diarsipkan')->success()->send();
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
