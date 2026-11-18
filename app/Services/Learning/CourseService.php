<?php

namespace App\Services\Learning;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseService
{
    public function __construct(private readonly EnrollmentService $enrollments) {}

    /**
     * Create a course for a teaching assignment. New courses always start as drafts.
     *
     * @param  array{title: string, description?: ?string, thumbnail_path?: ?string}  $data
     */
    public function create(TeacherAssignment $assignment, array $data): Course
    {
        return DB::transaction(fn (): Course => Course::query()->create([
            'teacher_assignment_id' => $assignment->getKey(),
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'description' => $data['description'] ?? null,
            'thumbnail_path' => $data['thumbnail_path'] ?? null,
            'status' => CourseStatus::Draft,
        ]));
    }

    /**
     * Update course metadata and guard status transitions.
     *
     * @param  array{title?: string, description?: ?string, thumbnail_path?: ?string, status?: CourseStatus|string}  $data
     */
    public function update(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data): Course {
            if (array_key_exists('title', $data) && $data['title'] !== $course->title) {
                $data['slug'] = $this->uniqueSlug($data['title'], $course->getKey());
            }

            if (array_key_exists('status', $data)) {
                $status = $data['status'] instanceof CourseStatus
                    ? $data['status']
                    : CourseStatus::from($data['status']);

                if ($status === CourseStatus::Published && ! $course->isPublished()) {
                    $this->assertPublishable($course);
                    $data['published_at'] = now();
                }

                $data['status'] = $status;

                if ($status !== CourseStatus::Published) {
                    $data['published_at'] = null;
                }
            }

            $course->update($data);

            if (($data['status'] ?? null) === CourseStatus::Published) {
                $this->enrollments->syncCourse($course);
            }

            return $course->refresh();
        });
    }

    /**
     * Publish a course and enroll the classroom members.
     */
    public function publish(Course $course): Course
    {
        $this->assertPublishable($course);

        return DB::transaction(function () use ($course): Course {
            $course->update([
                'status' => CourseStatus::Published,
                'published_at' => $course->published_at ?? now(),
            ]);

            $this->enrollments->syncCourse($course);

            return $course->refresh();
        });
    }

    public function unpublish(Course $course): Course
    {
        $course->update([
            'status' => CourseStatus::Draft,
            'published_at' => null,
        ]);

        return $course->refresh();
    }

    public function archive(Course $course): Course
    {
        $course->update([
            'status' => CourseStatus::Archived,
            'published_at' => null,
        ]);

        return $course->refresh();
    }

    public function delete(Course $course): void
    {
        DB::transaction(fn () => $course->delete());
    }

    /**
     * A course can only be published when at least one published lesson exists.
     */
    public function assertPublishable(Course $course): void
    {
        $hasPublishedLesson = $course->chapters()
            ->where('is_published', true)
            ->whereHas('lessons', fn ($query) => $query->where('is_published', true))
            ->exists();

        if (! $hasPublishedLesson) {
            throw ValidationException::withMessages([
                'status' => 'Kursus harus memiliki minimal satu bab dan materi yang sudah diterbitkan.',
            ]);
        }
    }

    /**
     * Build a slug that is unique even against soft-deleted courses.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'kursus';
        $slug = $base;
        $suffix = 2;

        while (Course::query()->withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
