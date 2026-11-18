<?php

namespace App\Filament\Resources\Materials\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class MaterialInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Material overview')
                    ->description('Cover image and basic information about this material.')
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('cover_image_path')
                            ->label('Cover image')
                            ->disk('public')
                            ->imageSize(240)
                            ->placeholder('No cover image')
                            ->columnSpan(1),
                        Group::make([
                            TextEntry::make('title')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->columnSpanFull(),
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->icon(Heroicon::OutlinedCalendarDays)
                                ->badge()
                                ->color('gray')
                                ->since()
                                ->sinceTooltip(),
                            TextEntry::make('updated_at')
                                ->label('Last updated')
                                ->icon(Heroicon::OutlinedArrowPath)
                                ->badge()
                                ->color('gray')
                                ->since()
                                ->sinceTooltip(),
                        ])
                            ->columns(2)
                            ->columnSpan(2),
                    ]),
                Section::make('Content')
                    ->description('The full material content.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->size(TextSize::Medium)
                            ->placeholder('No content has been added yet.'),
                    ]),
            ]);
    }
}
