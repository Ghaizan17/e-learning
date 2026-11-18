<?php

namespace App\Filament\Resources\Semesters\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SemesterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Semester')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('academicYear.name')
                            ->label('Tahun ajaran'),
                        TextEntry::make('name')
                            ->label('Semester')
                            ->badge()
                            ->formatStateUsing(fn ($state): string => $state?->label() ?? '-'),
                        TextEntry::make('sequence')
                            ->label('Urutan'),
                        TextEntry::make('start_date')
                            ->label('Mulai')
                            ->date('d F Y'),
                        TextEntry::make('end_date')
                            ->label('Selesai')
                            ->date('d F Y'),
                        IconEntry::make('is_active')
                            ->label('Aktif')
                            ->boolean(),
                    ]),
            ]);
    }
}
