<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\Academic\AcademicRolloverService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\ValidationException;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rollover')
                ->label('Duplikasi struktur')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->modalDescription('Menyalin semester dan kelas dari tahun ajaran aktif tanpa menyalin siswa.')
                ->visible(fn (): bool => AcademicYear::query()->active()->exists())
                ->schema([
                    TextInput::make('name')
                        ->label('Nama tahun ajaran baru')
                        ->placeholder('2027/2028')
                        ->required()
                        ->maxLength(20),
                    DatePicker::make('start_date')
                        ->label('Tanggal mulai')
                        ->required()
                        ->native(false),
                    DatePicker::make('end_date')
                        ->label('Tanggal selesai')
                        ->required()
                        ->native(false)
                        ->after('start_date'),
                ])
                ->action(function (array $data): void {
                    $source = AcademicYear::query()->active()->firstOrFail();

                    try {
                        $target = app(AcademicRolloverService::class)->rollover($source, $data);
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Struktur gagal diduplikasi')
                            ->body(collect($exception->errors())->flatten()->first())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Tahun ajaran '.$target->name.' dibuat')
                        ->body($target->classrooms()->count().' kelas disalin tanpa anggota.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
