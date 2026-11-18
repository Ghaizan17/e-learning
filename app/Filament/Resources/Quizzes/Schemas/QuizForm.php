<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kuis')
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
                        TextInput::make('title')->label('Judul')->required()->maxLength(150),
                        Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
                        Select::make('type')
                            ->label('Jenis')
                            ->options(['quiz' => 'Kuis', 'exam' => 'Ujian'])
                            ->required()
                            ->default('quiz'),
                        TextInput::make('duration_minutes')->label('Durasi (menit)')->numeric()->minValue(1)->maxValue(300)->required()->default(60),
                        DateTimePicker::make('starts_at')->label('Mulai')->seconds(false),
                        DateTimePicker::make('ends_at')->label('Selesai')->seconds(false),
                        TextInput::make('max_attempts')->label('Maksimal percobaan')->numeric()->minValue(1)->maxValue(10)->required()->default(1),
                        TextInput::make('passing_score')->label('Nilai kelulusan')->numeric()->minValue(0)->maxValue(100)->required()->default(70),
                        Toggle::make('shuffle_questions')->label('Acak soal'),
                        Toggle::make('shuffle_options')->label('Acak pilihan'),
                        Toggle::make('show_result_immediately')->label('Tampilkan hasil langsung')->default(true),
                    ]),
            ]);
    }
}
