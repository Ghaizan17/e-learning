<?php

namespace App\Filament\Resources\Assignments\Schemas;

use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tugas')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('course_id')
                            ->label('Kursus')
                            ->options(fn (): array => Course::query()
                                ->with(['teacherAssignment.classroom', 'teacherAssignment.subject'])
                                ->get()
                                ->mapWithKeys(fn (Course $course): array => [
                                    $course->getKey() => implode(' · ', array_filter([
                                        $course->title,
                                        $course->teacherAssignment?->classroom?->name,
                                        $course->teacherAssignment?->subject?->name,
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
                        Textarea::make('instructions')
                            ->label('Instruksi')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                        DateTimePicker::make('due_at')
                            ->label('Tenggat')
                            ->required()
                            ->seconds(false)
                            ->minutesStep(5),
                        TextInput::make('max_score')
                            ->label('Skor maksimal')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(100)
                            ->required(),
                        Toggle::make('allow_resubmission')
                            ->label('Izinkan pengumpulan ulang')
                            ->inline(false),
                    ]),
            ]);
    }
}
