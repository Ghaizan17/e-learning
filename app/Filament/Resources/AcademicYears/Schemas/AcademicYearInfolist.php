<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicYearInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tahun ajaran')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('start_date')
                            ->label('Mulai')
                            ->date('d F Y'),
                        TextEntry::make('end_date')
                            ->label('Selesai')
                            ->date('d F Y'),
                        IconEntry::make('is_active')
                            ->label('Aktif')
                            ->boolean(),
                        TextEntry::make('semesters_count')
                            ->label('Semester')
                            ->state(fn ($record): int => $record->semesters()->count()),
                        TextEntry::make('classrooms_count')
                            ->label('Kelas')
                            ->state(fn ($record): int => $record->classrooms()->count()),
                    ]),
            ]);
    }
}
