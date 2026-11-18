<?php

use App\Enums\EnrollmentStatus;
use App\Enums\LessonProgressStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonAttachment;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\Learning\ChapterService;
use App\Services\Learning\CourseService;
use App\Services\Learning\EnrollmentService;
use App\Services\Learning\LessonService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 2);
    $this->student = $this->fixture->students[0];
});

it('shows a teacher their courses and classes', function (): void {
    $this->actingAs($this->fixture->teacher)
        ->get('/teacher/courses')
        ->assertOk()
        ->assertSee('Pemrograman Web Dasar');

    $this->actingAs($this->fixture->teacher)
        ->get('/teacher/classes')
        ->assertOk()
        ->assertSee('XI PPLG A');
});

it('creates a course from the teacher portal', function (): void {
    $this->actingAs($this->fixture->teacher)
        ->post('/teacher/courses', [
            'teacher_assignment_id' => $this->fixture->assignment->getKey(),
            'title' => 'Kursus Baru',
            'description' => 'Deskripsi kursus baru.',
        ])
        ->assertRedirect();

    $course = Course::query()->where('title', 'Kursus Baru')->firstOrFail();

    expect($course->status->value)->toBe('draft')
        ->and($course->teacher_assignment_id)->toBe($this->fixture->assignment->getKey())
        ->and($course->slug)->toBe('kursus-baru');
});

it('refuses to publish a course without published lessons', function (): void {
    $this->actingAs($this->fixture->teacher)
        ->post('/teacher/courses/'.$this->fixture->course->getKey().'/publish')
        ->assertSessionHasErrors('status');

    expect($this->fixture->course->fresh()->status->value)->toBe('draft');
});

it('publishes a course and syncs students from the teacher portal', function (): void {
    app(ChapterService::class)->publish($this->fixture->chapter);
    app(LessonService::class)->publish($this->fixture->lesson);
    app(CourseService::class)->publish($this->fixture->course);

    $this->actingAs($this->fixture->teacher)
        ->post('/teacher/courses/'.$this->fixture->course->getKey().'/sync-enrollments')
        ->assertRedirect();

    expect(Enrollment::query()->where('course_id', $this->fixture->course->getKey())->count())->toBe(2);

    $this->actingAs($this->fixture->teacher)
        ->get('/teacher/courses/'.$this->fixture->course->getKey().'/students')
        ->assertOk()
        ->assertSee('Siswa 1');
});

it('only lists published courses to students', function (): void {
    app(EnrollmentService::class)->enroll($this->student, $this->fixture->course);

    $this->actingAs($this->student)
        ->get('/student/courses')
        ->assertOk()
        ->assertDontSee('Pemrograman Web Dasar');

    $this->fixture->publishAll();

    $this->actingAs($this->student)
        ->get('/student/courses')
        ->assertOk()
        ->assertSee('Pemrograman Web Dasar');
});

it('hides a draft course from a student even when enrolled', function (): void {
    app(EnrollmentService::class)->enroll($this->student, $this->fixture->course);

    $this->actingAs($this->student)
        ->get('/student/courses/'.$this->fixture->course->getKey())
        ->assertForbidden();
});

it('records lesson progress and finishes the course at 100 percent', function (): void {
    $this->fixture->publishAll();

    $this->actingAs($this->student)
        ->get('/student/courses/'.$this->fixture->course->getKey().'/lessons/'.$this->fixture->lesson->getKey())
        ->assertOk()
        ->assertSee('Struktur Dasar');

    expect(LessonProgress::query()
        ->where('user_id', $this->student->getKey())
        ->where('lesson_id', $this->fixture->lesson->getKey())
        ->first()?->status)->toBe(LessonProgressStatus::InProgress);

    $this->actingAs($this->student)
        ->post('/student/lessons/'.$this->fixture->lesson->getKey().'/complete')
        ->assertRedirect();

    $enrollment = Enrollment::query()->where('user_id', $this->student->getKey())->firstOrFail();

    expect(LessonProgress::query()->where('user_id', $this->student->getKey())->completed()->count())->toBe(1)
        ->and($enrollment->status)->toBe(EnrollmentStatus::Completed);
});

it('denies a student access to lessons of another classroom', function (): void {
    $this->fixture->publishAll();

    $outsider = User::factory()->student()->create();

    $this->actingAs($outsider)
        ->get('/student/courses/'.$this->fixture->course->getKey().'/lessons/'.$this->fixture->lesson->getKey())
        ->assertForbidden();
});

it('streams attachments only to enrolled students', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('lesson-attachments/materi.pdf', 'isi materi');

    $attachment = LessonAttachment::factory()->create([
        'lesson_id' => $this->fixture->lesson->getKey(),
        'file_path' => 'lesson-attachments/materi.pdf',
        'disk' => 'local',
    ]);

    $this->fixture->publishAll();

    $this->actingAs($this->student)
        ->get('/attachments/'.$attachment->getKey().'/download')
        ->assertOk();

    $outsider = User::factory()->student()->create();

    $this->actingAs($outsider)
        ->get('/attachments/'.$attachment->getKey().'/download')
        ->assertForbidden();
});

it('lets a teacher upload and delete a private attachment', function (): void {
    Storage::fake('local');

    $this->fixture->publishAll();

    $this->actingAs($this->fixture->teacher)
        ->post('/teacher/lessons/'.$this->fixture->lesson->getKey().'/attachments', [
            'title' => 'Modul HTML',
            'file' => UploadedFile::fake()->create('modul.pdf', 120, 'application/pdf'),
        ])
        ->assertRedirect();

    $attachment = $this->fixture->lesson->attachments()->firstOrFail();

    Storage::disk('local')->assertExists($attachment->file_path);

    $this->actingAs($this->fixture->teacher)
        ->delete('/teacher/attachments/'.$attachment->getKey())
        ->assertRedirect();

    Storage::disk('local')->assertMissing($attachment->file_path);
    expect($this->fixture->lesson->attachments()->count())->toBe(0);
});

it('blocks teachers from managing a course they do not own', function (): void {
    $other = User::factory()->teacher()->create();

    $this->actingAs($other)
        ->get('/teacher/courses/'.$this->fixture->course->getKey().'/edit')
        ->assertForbidden();
});
