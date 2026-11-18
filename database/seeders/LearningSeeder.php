<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\TeacherAssignment;
use App\Services\Learning\ChapterService;
use App\Services\Learning\CourseService;
use App\Services\Learning\LessonService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LearningSeeder extends Seeder
{
    /**
     * Seed realistic courses, chapters, and lessons for local development.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('LearningSeeder skipped: production environment.');

            return;
        }

        $courses = app(CourseService::class);
        $chapters = app(ChapterService::class);
        $lessons = app(LessonService::class);

        $blueprints = [
            [
                'assignment' => ['guru@school.test', 'PWEB', 'XI PPLG A'],
                'title' => 'Pemrograman Web Dasar',
                'description' => 'Membangun halaman web pertama dengan HTML dan CSS.',
                'publish' => true,
                'chapters' => [
                    [
                        'title' => 'Pengenalan HTML',
                        'description' => 'Struktur dokumen dan elemen dasar HTML.',
                        'lessons' => [
                            [
                                'title' => 'Struktur Dasar Halaman HTML',
                                'type' => 'text',
                                'content' => '<p>Dokumen HTML terdiri dari <code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, dan <code>&lt;body&gt;</code>.</p><p>Simpan berkas sebagai <code>index.html</code> lalu buka di peramban.</p>',
                                'duration' => 15,
                            ],
                            [
                                'title' => 'Tag, Atribut, dan Elemen',
                                'type' => 'text',
                                'content' => '<p>Tag memberi makna pada konten. Atribut menambahkan informasi seperti <code>href</code> pada tautan dan <code>src</code> pada gambar.</p>',
                                'duration' => 20,
                            ],
                            [
                                'title' => 'Latihan Membuat Halaman Profil',
                                'type' => 'code',
                                'language' => 'html',
                                'content' => '<pre><code>&lt;h1&gt;Profil Siswa&lt;/h1&gt;
&lt;p&gt;Halo, saya siswa XI PPLG A.&lt;/p&gt;</code></pre>',
                                'duration' => 30,
                            ],
                            [
                                'title' => 'Referensi HTML MDN',
                                'type' => 'link',
                                'external_url' => 'https://developer.mozilla.org/id/docs/Web/HTML',
                                'duration' => 10,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Dasar CSS',
                        'description' => 'Memilih elemen, mengatur warna, dan menyusun tata letak.',
                        'lessons' => [
                            [
                                'title' => 'Selector dan Properti CSS',
                                'type' => 'text',
                                'content' => '<p>Selector menentukan elemen yang ditata. Properti seperti <code>color</code> dan <code>font-size</code> mengubah tampilannya.</p>',
                                'duration' => 20,
                            ],
                            [
                                'title' => 'Latihan Tema Warna Kelas',
                                'type' => 'code',
                                'language' => 'css',
                                'content' => '<pre><code>body {
  font-family: system-ui, sans-serif;
  background: #f8fafc;
}</code></pre>',
                                'duration' => 25,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'assignment' => ['sari@school.test', 'MTK', 'XI PPLG A'],
                'title' => 'Matematika Wajib XI',
                'description' => 'Barisan, deret, dan penerapan dalam pemrograman.',
                'publish' => true,
                'chapters' => [
                    [
                        'title' => 'Barisan dan Deret',
                        'description' => 'Pola bilangan aritmetika dan geometri.',
                        'lessons' => [
                            [
                                'title' => 'Barisan Aritmetika',
                                'type' => 'text',
                                'content' => '<p>Barisan aritmetika memiliki beda tetap: <code>Un = a + (n - 1)b</code>.</p>',
                                'duration' => 25,
                            ],
                            [
                                'title' => 'Latihan Deret Aritmetika',
                                'type' => 'text',
                                'content' => '<p>Hitung jumlah 10 suku pertama jika <code>a = 3</code> dan <code>b = 4</code>.</p>',
                                'duration' => 30,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'assignment' => ['rudi@school.test', 'BDATA', 'XI PPLG A'],
                'title' => 'Basis Data Lanjutan',
                'description' => 'Normalisasi tabel dan query gabungan.',
                'publish' => false,
                'chapters' => [
                    [
                        'title' => 'Normalisasi',
                        'description' => 'Dari tabel datar menuju struktur relasional.',
                        'lessons' => [
                            [
                                'title' => 'Konsep Normalisasi 1NF sampai 3NF',
                                'type' => 'text',
                                'content' => '<p>Normalisasi mengurangi duplikasi data dengan memecah tabel berdasarkan ketergantungan fungsional.</p>',
                                'duration' => 35,
                                'published' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($blueprints as $blueprint) {
            [$teacherEmail, $subjectCode, $classroomName] = $blueprint['assignment'];

            $assignment = TeacherAssignment::query()
                ->whereHas('teacher', fn ($query) => $query->where('email', $teacherEmail))
                ->whereHas('subject', fn ($query) => $query->where('code', $subjectCode))
                ->whereHas('classroom', fn ($query) => $query->where('name', $classroomName))
                ->first();

            if ($assignment === null) {
                $this->command?->warn('LearningSeeder skipped course '.$blueprint['title'].': assignment not found.');

                continue;
            }

            if (Course::query()->where('teacher_assignment_id', $assignment->getKey())->where('title', $blueprint['title'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($courses, $chapters, $lessons, $assignment, $blueprint): void {
                $course = $courses->create($assignment, [
                    'title' => $blueprint['title'],
                    'description' => $blueprint['description'],
                ]);

                foreach ($blueprint['chapters'] as $chapterData) {
                    $chapter = $chapters->create($course, [
                        'title' => $chapterData['title'],
                        'description' => $chapterData['description'],
                    ]);

                    $chapters->publish($chapter);

                    foreach ($chapterData['lessons'] as $lessonData) {
                        $lesson = $lessons->create($chapter, [
                            'title' => $lessonData['title'],
                            'type' => $lessonData['type'],
                            'content_html' => $lessonData['content'] ?? null,
                            'external_url' => $lessonData['external_url'] ?? null,
                            'code_language' => $lessonData['language'] ?? null,
                            'estimated_duration_minutes' => $lessonData['duration'] ?? null,
                        ]);

                        if ($lessonData['published'] ?? true) {
                            $lessons->publish($lesson);
                        }
                    }
                }

                if ($blueprint['publish']) {
                    $courses->publish($course);
                }
            });
        }

        $this->command?->info('Learning content seeded.');
    }
}
