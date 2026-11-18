<?php

namespace App\Filament\Resources\Grades\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GradeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nilai')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('student.name')->label('Siswa'),
                        TextEntry::make('course.title')->label('Kursus'),
                        TextEntry::make('component.title')->label('Komponen'),
                        TextEntry::make('component.category.name')->label('Kategori')->badge(),
                        TextEntry::make('score')->label('Skor'),
                        TextEntry::make('max_score')->label('Skor maksimal'),
                        TextEntry::make('percentage')->label('Persentase')->suffix('%'),
                        IconEntry::make('is_published')->label('Tampil ke siswa')->boolean(),
                        TextEntry::make('grader.name')->label('Dinilai oleh')->placeholder('-'),
                        TextEntry::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i'),
                    ]),
            ]);
    }
}
