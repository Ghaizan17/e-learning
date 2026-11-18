<?php

namespace App\Filament\Resources\AcademicYears\RelationManagers;

use App\Enums\SemesterName;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SemestersRelationManager extends RelationManager
{
    protected static string $relationship = 'semesters';

    protected static ?string $title = 'Semester';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
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
                        )),
                    Hidden::make('sequence')
                        ->default(SemesterName::Odd->sequence())
                        ->dehydrated(),
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
                        ->helperText('Hanya satu semester yang aktif di seluruh sekolah.')
                        ->inline(false),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Semester')
                    ->badge()
                    ->formatStateUsing(fn (SemesterName $state): string => $state->label())
                    ->color(fn (SemesterName $state): string => $state === SemesterName::Odd ? 'info' : 'success'),
                TextColumn::make('sequence')
                    ->label('Urutan')
                    ->alignCenter(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y'),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah semester')
                    ->mutateDataUsing(fn (array $data): array => $data + [
                        'academic_year_id' => $this->getOwnerRecord()->getKey(),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
