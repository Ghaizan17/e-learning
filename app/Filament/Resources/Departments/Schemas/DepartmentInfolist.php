<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program keahlian')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('code')
                            ->label('Kode')
                            ->badge(),
                        TextEntry::make('subjects_count')
                            ->label('Mata pelajaran')
                            ->state(fn ($record): int => $record->subjects()->count()),
                        TextEntry::make('classrooms_count')
                            ->label('Kelas')
                            ->state(fn ($record): int => $record->classrooms()->count()),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
