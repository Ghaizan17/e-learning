<?php

namespace Tests\Support;

use App\Models\AcademicYear;
use App\Models\Chapter;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Academic\ClassroomService;
use App\Services\Academic\TeacherAssignmentService;
use App\Services\Learning\ChapterService;
use App\Services\Learning\CourseService;
use App\Services\Learning\LessonService;

/**
 * Builds a consistent academic + learning dataset for feature tests.
 */
class LearningFixture
{
    public AcademicYear $year;

    public Semester $semester;

    public Classroom $classroom;

    public Subject $subject;

    public User $teacher;

    public TeacherAssignment $assignment;

    /** @var array<int, User> */
    public array $students = [];

    public Course $course;

    public Chapter $chapter;

    public Lesson $lesson;

    /**
     * @param  array<int, string>  $studentNames
     */
    public static function make(int $studentCount = 2): self
    {
        $fixture = new self;

        $fixture->year = AcademicYear::factory()->active()->create(['name' => '2026/2027']);
        $fixture->semester = Semester::factory()->forYear($fixture->year)->odd()->active()->create();
        $fixture->classroom = Classroom::factory()->forYear($fixture->year)->create([
            'name' => 'XI PPLG A',
            'capacity' => 40,
        ]);
        $fixture->subject = Subject::factory()->create(['name' => 'Pemrograman Web']);
        $fixture->teacher = User::factory()->teacher()->create(['name' => 'Bu Rina']);

        $fixture->assignment = app(TeacherAssignmentService::class)->assign(
            $fixture->teacher,
            $fixture->classroom,
            $fixture->subject,
            $fixture->year,
            $fixture->semester,
        );

        for ($index = 0; $index < $studentCount; $index++) {
            $student = User::factory()->student()->create(['name' => 'Siswa '.($index + 1)]);
            app(ClassroomService::class)->enroll($student, $fixture->classroom);
            $fixture->students[] = $student;
        }

        $fixture->course = app(CourseService::class)->create($fixture->assignment, [
            'title' => 'Pemrograman Web Dasar',
            'description' => 'Kursus pengantar.',
        ]);

        $fixture->chapter = app(ChapterService::class)->create($fixture->course, [
            'title' => 'Pengenalan HTML',
        ]);

        $fixture->lesson = app(LessonService::class)->create($fixture->chapter, [
            'title' => 'Struktur Dasar',
            'content_html' => '<p>Isi materi.</p>',
        ]);

        return $fixture;
    }

    /**
     * Publish the chapter, lesson, and course so students can access it.
     */
    public function publishAll(): void
    {
        app(ChapterService::class)->publish($this->chapter);
        app(LessonService::class)->publish($this->lesson);
        app(CourseService::class)->publish($this->course);
    }
}
