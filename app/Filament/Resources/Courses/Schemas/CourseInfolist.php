<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kursus')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')->label('Judul'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('teacherAssignment.teacher.name')->label('Guru'),
                        TextEntry::make('teacherAssignment.classroom.name')->label('Kelas'),
                        TextEntry::make('teacherAssignment.subject.name')->label('Mata pelajaran'),
                        TextEntry::make('teacherAssignment.semester.name')
                            ->label('Semester')
                            ->formatStateUsing(fn ($state): string => $state?->label() ?? '-'),
                        TextEntry::make('published_at')
                            ->label('Diterbitkan')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
