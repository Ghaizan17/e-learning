<?php

namespace App\Filament\Resources\GradingCategories\Schemas;

use App\Enums\GradingCategoryName;
use App\Models\Course;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GradingCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategori nilai')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('course_id')
                            ->label('Kursus')
                            ->options(fn (): array => Course::query()->orderBy('title')->pluck('title', 'id')->all())
                            ->searchable()
                            ->required(),
                        Select::make('name')
                            ->label('Kategori')
                            ->options(collect(GradingCategoryName::cases())->mapWithKeys(
                                fn (GradingCategoryName $name): array => [$name->value => $name->label()],
                            ))
                            ->required(),
                        TextInput::make('weight')
                            ->label('Bobot (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->default(0),
                    ]),
            ]);
    }
}
