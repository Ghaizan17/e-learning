<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Enums\LessonType;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Materi')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('chapter_id')
                            ->label('Bab')
                            ->relationship('chapter', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => ($record->course?->title ?? '-').' · '.$record->title)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),
                        Select::make('type')
                            ->label('Tipe')
                            ->options(collect(LessonType::cases())->mapWithKeys(
                                fn (LessonType $type): array => [$type->value => $type->label()],
                            ))
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('estimated_duration_minutes')
                            ->label('Estimasi (menit)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(600),
                        RichEditor::make('content_html')
                            ->label('Konten materi')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3'],
                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->columnSpanFull(),
                        TextInput::make('video_url')
                            ->label('URL video')
                            ->url()
                            ->maxLength(2048),
                        TextInput::make('external_url')
                            ->label('URL eksternal')
                            ->url()
                            ->maxLength(2048),
                        TextInput::make('code_language')
                            ->label('Bahasa kode')
                            ->maxLength(40),
                        Toggle::make('is_published')
                            ->label('Terbitkan materi')
                            ->inline(false),
                    ]),
            ]);
    }
}
