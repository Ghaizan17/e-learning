<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use App\Enums\GradeLevel;
use App\Enums\RoleName;
use App\Models\AcademicYear;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas kelas')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('Tahun ajaran')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn (): ?int => AcademicYear::query()->active()->value('id')),
                        TextInput::make('name')
                            ->label('Nama kelas')
                            ->placeholder('XI PPLG A')
                            ->required()
                            ->maxLength(80)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule, callable $get) => $rule->where('academic_year_id', $get('academic_year_id')),
                            ),
                        Select::make('grade_level')
                            ->label('Tingkat')
                            ->options(collect(GradeLevel::cases())->mapWithKeys(
                                fn (GradeLevel $level): array => [$level->value => $level->label()],
                            ))
                            ->required(),
                        Select::make('department_id')
                            ->label('Program keahlian')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Umum'),
                        Select::make('homeroom_teacher_id')
                            ->label('Wali kelas')
                            ->relationship(
                                'homeroomTeacher',
                                'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->whereHas(
                                    'roles',
                                    fn (Builder $roles): Builder => $roles->whereIn('name', RoleName::teacherValues()),
                                ),
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Belum ditentukan'),
                        TextInput::make('capacity')
                            ->label('Kapasitas')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(32)
                            ->required(),
                    ]),
            ]);
    }
}
