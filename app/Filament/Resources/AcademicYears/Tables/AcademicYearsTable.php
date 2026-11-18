<?php

namespace App\Filament\Resources\AcademicYears\Tables;

use App\Services\Academic\AcademicYearService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AcademicYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tahun ajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('semesters_count')
                    ->label('Semester')
                    ->counts('semesters')
                    ->alignCenter(),
                TextColumn::make('classrooms_count')
                    ->label('Kelas')
                    ->counts('classrooms')
                    ->alignCenter(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua tahun')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak aktif'),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Tahun ajaran lain akan otomatis dinonaktifkan.')
                    ->visible(fn ($record): bool => ! $record->is_active)
                    ->action(function ($record): void {
                        app(AcademicYearService::class)->activate($record);

                        Notification::make()
                            ->title('Tahun ajaran '.$record->name.' diaktifkan')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record): bool => $record->classrooms()->doesntExist())
                    ->tooltip('Tahun ajaran yang memiliki kelas tidak dapat dihapus.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
