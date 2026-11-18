<?php

namespace App\Filament\Resources\Assignments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tugas')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')->label('Judul'),
                        TextEntry::make('course.title')->label('Kursus'),
                        TextEntry::make('due_at')->label('Tenggat')->dateTime('d M Y H:i'),
                        TextEntry::make('max_score')->label('Skor maksimal'),
                        TextEntry::make('is_published')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Terbit' : 'Draf')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('allow_resubmission')
                            ->label('Pengumpulan ulang')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Diizinkan' : 'Tidak'),
                        TextEntry::make('instructions')
                            ->label('Instruksi')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
