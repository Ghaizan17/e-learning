<?php

namespace App\Filament\Resources\TeacherAssignments\Schemas;

use App\Enums\SemesterName;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeacherAssignmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penugasan mengajar')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('academicYear.name')
                            ->label('Tahun ajaran'),
                        TextEntry::make('semester.name')
                            ->label('Semester')
                            ->formatStateUsing(fn (SemesterName $state): string => $state->label()),
                        TextEntry::make('teacher.name')
                            ->label('Guru'),
                        TextEntry::make('subject.name')
                            ->label('Mata pelajaran'),
                        TextEntry::make('classroom.name')
                            ->label('Kelas'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),
                    ]),
            ]);
    }
}
