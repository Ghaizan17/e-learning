<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mata pelajaran')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('code')
                            ->label('Kode')
                            ->badge(),
                        TextEntry::make('department.name')
                            ->label('Program keahlian')
                            ->placeholder('Umum'),
                        TextEntry::make('teacher_assignments_count')
                            ->label('Penugasan mengajar')
                            ->state(fn ($record): int => $record->teacherAssignments()->count()),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
