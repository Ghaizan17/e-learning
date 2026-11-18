<?php

namespace App\Filament\Resources\TeacherAssignments\Schemas;

use App\Enums\RoleName;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\TeacherAssignment;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TeacherAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penugasan mengajar')
                    ->columns(1)
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('Tahun ajaran')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->default(fn (): ?int => AcademicYear::query()->active()->value('id'))
                            ->afterStateUpdated(fn (callable $set) => $set('classroom_id', null)),
                        Select::make('semester_id')
                            ->label('Semester')
                            ->options(fn (callable $get): array => Semester::query()
                                ->where('academic_year_id', $get('academic_year_id'))
                                ->orderBy('sequence')
                                ->get()
                                ->mapWithKeys(fn (Semester $semester): array => [
                                    $semester->getKey() => $semester->name->label(),
                                ])
                                ->all())
                            ->required()
                            ->searchable(),
                        Select::make('teacher_id')
                            ->label('Guru')
                            ->relationship(
                                'teacher',
                                'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->whereHas(
                                    'roles',
                                    fn (Builder $roles): Builder => $roles->whereIn('name', RoleName::teacherValues()),
                                ),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rule(fn (callable $get, $livewire): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $livewire): void {
                                $recordId = $livewire instanceof EditRecord ? $livewire->getRecord()?->getKey() : null;

                                $duplicate = TeacherAssignment::query()
                                    ->when($recordId !== null, fn ($query) => $query->whereKeyNot($recordId))
                                    ->where('teacher_id', $value)
                                    ->where('classroom_id', $get('classroom_id'))
                                    ->where('subject_id', $get('subject_id'))
                                    ->where('semester_id', $get('semester_id'))
                                    ->exists();

                                if ($duplicate) {
                                    $fail('Penugasan guru ini sudah ada untuk kelas, mata pelajaran, dan semester tersebut.');
                                }
                            }),
                        Select::make('subject_id')
                            ->label('Mata pelajaran')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('classroom_id')
                            ->label('Kelas')
                            ->options(fn (callable $get): array => Classroom::query()
                                ->where('academic_year_id', $get('academic_year_id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->helperText('Daftar kelas mengikuti tahun ajaran yang dipilih.'),
                    ]),
            ]);
    }
}
