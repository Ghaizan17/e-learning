<?php

namespace App\Filament\Resources\Semesters\Schemas;

use App\Enums\SemesterName;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SemesterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail semester')
                    ->columns(1)
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('Tahun ajaran')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('name')
                            ->label('Semester')
                            ->options(collect(SemesterName::cases())->mapWithKeys(
                                fn (SemesterName $semester): array => [$semester->value => $semester->label()],
                            ))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set(
                                'sequence',
                                $state !== null ? SemesterName::from($state)->sequence() : 1,
                            ))
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule, callable $get) => $rule->where('academic_year_id', $get('academic_year_id')),
                            ),
                        Hidden::make('sequence')->default(SemesterName::Odd->sequence())->dehydrated(),
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
                        Toggle::make('is_active')
                            ->label('Semester aktif')
                            ->helperText('Mengaktifkan semester ini akan menonaktifkan semester lain.')
                            ->inline(false),
                    ]),
            ]);
    }
}
