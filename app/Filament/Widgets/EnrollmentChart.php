<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Widgets\ChartWidget;

class EnrollmentChart extends ChartWidget
{
    protected ?string $heading = 'Pendaftaran per kursus';

    protected ?string $description = 'Kursus dengan peserta terbanyak.';

    protected ?string $maxHeight = '240px';

    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 6;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $options = [
        'plugins' => [
            'legend' => ['display' => false],
        ],
        'scales' => [
            'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
        ],
    ];

    protected function getData(): array
    {
        $courses = Course::query()
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit(6)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Siswa terdaftar',
                    'data' => $courses->pluck('enrollments_count')->all(),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $courses->map(fn (Course $course): string => str($course->title)->limit(22)->toString())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
