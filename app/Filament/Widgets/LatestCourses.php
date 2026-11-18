<?php

namespace App\Filament\Widgets;

use App\Enums\CourseStatus;
use App\Models\Course;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestCourses extends TableWidget
{
    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Kursus terbaru')
            ->description('Kursus yang baru dibuat beserta kesiapan kelasnya.')
            ->query(fn (): Builder => Course::query()
                ->with(['teacherAssignment.classroom', 'teacherAssignment.subject'])
                ->withCount(['chapters', 'enrollments'])
                ->latest())
            ->columns([
                TextColumn::make('title')
                    ->label('Kursus')
                    ->description(fn (Course $record): string => collect([
                        $record->teacherAssignment?->subject?->name,
                        $record->teacherAssignment?->classroom?->name,
                    ])->filter()->implode(' · '))
                    ->wrap()
                    ->grow(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (CourseStatus $state): string => $state->label())
                    ->color(fn (CourseStatus $state): string => $state->color()),
                TextColumn::make('chapters_count')
                    ->label('Bab')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
                TextColumn::make('enrollments_count')
                    ->label('Siswa')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->color('gray')
                    ->size('sm'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada kursus')
            ->emptyStateDescription('Kursus yang dibuat akan tampil di sini.');
    }
}
