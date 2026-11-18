<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kuis')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')->label('Judul'),
                        TextEntry::make('course.title')->label('Kursus'),
                        TextEntry::make('type')->label('Jenis')->badge(),
                        TextEntry::make('duration_minutes')->label('Durasi (menit)'),
                        TextEntry::make('starts_at')->label('Mulai')->dateTime('d M Y H:i')->placeholder('-'),
                        TextEntry::make('ends_at')->label('Selesai')->dateTime('d M Y H:i')->placeholder('-'),
                        TextEntry::make('max_attempts')->label('Maksimal percobaan'),
                        TextEntry::make('passing_score')->label('Nilai kelulusan'),
                        TextEntry::make('is_published')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Terbit' : 'Draf')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),
            ]);
    }
}
