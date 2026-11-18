<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Services\Learning\AssignmentService;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Seed realistic assignments for the demo courses (non-production only).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $service = app(AssignmentService::class);

        $blueprints = [
            [
                'course' => 'Pemrograman Web Dasar',
                'title' => 'Membuat Halaman Profil dengan HTML',
                'instructions' => "Buat satu halaman HTML berisi profil diri dengan ketentuan:\n".
                    "1. Judul halaman berisi nama lengkap.\n".
                    "2. Minimal satu gambar dan satu tautan.\n".
                    "3. Gunakan elemen semantik (header, main, footer).\n\n".
                    'Kumpulkan berkas .html atau tautan repositori Anda.',
                'due_at' => now()->addDays(7)->setTime(23, 59),
                'allow_resubmission' => true,
            ],
            [
                'course' => 'Pemrograman Web Dasar',
                'title' => 'Latihan Tema Warna CSS',
                'instructions' => 'Terapkan tema warna kelas pada halaman profil: palet 3 warna, '.
                    'kontras teks memadai, dan gunakan variabel CSS untuk warna.',
                'due_at' => now()->addDays(14)->setTime(23, 59),
                'allow_resubmission' => false,
            ],
            [
                'course' => 'Matematika Wajib XI',
                'title' => 'Latihan Barisan Aritmetika',
                'instructions' => 'Kerjakan 5 soal barisan aritmetika dari lampiran. '.
                    'Tuliskan langkah penyelesaian, bukan hanya jawaban akhir.',
                'due_at' => now()->addDays(5)->setTime(20, 0),
                'allow_resubmission' => false,
            ],
        ];

        foreach ($blueprints as $blueprint) {
            $course = Course::query()->where('title', $blueprint['course'])->first();

            if ($course === null) {
                continue;
            }

            $assignment = Assignment::query()->firstOrCreate(
                ['course_id' => $course->getKey(), 'title' => $blueprint['title']],
                [
                    'instructions' => $blueprint['instructions'],
                    'due_at' => $blueprint['due_at'],
                    'max_score' => 100,
                    'allow_resubmission' => $blueprint['allow_resubmission'],
                    'is_published' => false,
                ],
            );

            if (! $assignment->is_published) {
                $service->publish($assignment);
            }
        }

        $this->command?->info('Assignments seeded.');
    }
}
