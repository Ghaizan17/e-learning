<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use App\Enums\GradeLevel;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClassroomInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kelas')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama kelas'),
                        TextEntry::make('academicYear.name')
                            ->label('Tahun ajaran'),
                        TextEntry::make('grade_level')
                            ->label('Tingkat')
                            ->formatStateUsing(fn (GradeLevel $state): string => $state->label()),
                        TextEntry::make('department.name')
                            ->label('Program keahlian')
                            ->placeholder('Umum'),
                        TextEntry::make('homeroomTeacher.name')
                            ->label('Wali kelas')
                            ->placeholder('Belum ditentukan'),
                        TextEntry::make('capacity')
                            ->label('Kapasitas'),
                        TextEntry::make('active_members_count')
                            ->label('Siswa aktif')
                            ->state(fn ($record): string => $record->activeMembers()->count().' / '.$record->capacity),
                        TextEntry::make('teacher_assignments_count')
                            ->label('Penugasan mengajar')
                            ->state(fn ($record): int => $record->teacherAssignments()->count()),
                    ]),
            ]);
    }
}
