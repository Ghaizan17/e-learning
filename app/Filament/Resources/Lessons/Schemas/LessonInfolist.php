<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Enums\LessonType;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Materi')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')->label('Judul'),
                        TextEntry::make('chapter.course.title')->label('Kursus'),
                        TextEntry::make('chapter.title')->label('Bab'),
                        TextEntry::make('type')
                            ->label('Tipe')
                            ->formatStateUsing(fn (LessonType $state): string => $state->label()),
                        TextEntry::make('estimated_duration_minutes')
                            ->label('Estimasi')
                            ->suffix(' menit')
                            ->placeholder('-'),
                        IconEntry::make('is_published')->label('Terbit')->boolean(),
                        TextEntry::make('content_html')
                            ->label('Konten')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
