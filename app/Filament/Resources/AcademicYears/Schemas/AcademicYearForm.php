<?php

namespace App\Filament\Resources\AcademicYears\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcademicYearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas tahun ajaran')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama tahun ajaran')
                            ->placeholder('2026/2027')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->helperText('Gunakan format YYYY/YYYY.'),
                        Toggle::make('is_active')
                            ->label('Tahun ajaran aktif')
                            ->helperText('Mengaktifkan tahun ini akan menonaktifkan tahun ajaran lain.')
                            ->inline(false),
                        DatePicker::make('start_date')
                            ->label('Tanggal mulai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('end_date')
                            ->label('Tanggal selesai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('start_date'),
                    ]),
            ]);
    }
}
