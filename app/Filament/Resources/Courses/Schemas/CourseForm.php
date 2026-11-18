<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\TeacherAssignment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kursus')
                    ->columns(1)
                    ->schema([
                        Select::make('teacher_assignment_id')
                            ->label('Penugasan mengajar')
                            ->options(fn (): array => TeacherAssignment::query()
                                ->with(['teacher', 'classroom', 'subject', 'semester'])
                                ->get()
                                ->mapWithKeys(fn (TeacherAssignment $assignment): array => [
                                    $assignment->getKey() => implode(' · ', array_filter([
                                        $assignment->teacher?->name,
                                        $assignment->classroom?->name,
                                        $assignment->subject?->name,
                                        $assignment->semester?->name?->label(),
                                    ])),
                                ])
                                ->all())
                            ->searchable()
                            ->required()
                            ->disabledOn('edit'),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(150),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                        FileUpload::make('thumbnail_path')
                            ->label('Sampul')
                            ->image()
                            ->disk('public')
                            ->directory('course-thumbnails')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
