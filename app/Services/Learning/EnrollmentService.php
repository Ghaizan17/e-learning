<?php

namespace App\Services\Learning;

use App\Enums\ClassroomMemberStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\RoleName;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    /**
     * Enroll a student in a course, idempotently.
     */
    public function enroll(User $student, Course $course): Enrollment
    {
        if (! $student->hasRole(RoleName::Student->value)) {
            throw ValidationException::withMessages([
                'user_id' => 'Pengguna yang dipilih bukan siswa.',
            ]);
        }

        return DB::transaction(function () use ($student, $course): Enrollment {
            $enrollment = Enrollment::query()
                ->where('course_id', $course->getKey())
                ->where('user_id', $student->getKey())
                ->first();

            if ($enrollment !== null) {
                if ($enrollment->status === EnrollmentStatus::Dropped) {
                    $enrollment->update([
                        'status' => EnrollmentStatus::Active,
                        'enrolled_at' => now(),
                        'completed_at' => null,
                    ]);
                }

                return $enrollment->refresh();
            }

            return Enrollment::query()->create([
                'course_id' => $course->getKey(),
                'user_id' => $student->getKey(),
                'enrolled_at' => now(),
                'status' => EnrollmentStatus::Active,
            ]);
        });
    }

    /**
     * Sync the active students of the course classroom into enrollments.
     *
     * Existing enrollments are never downgraded, so progress survives re-syncs.
     */
    public function syncCourse(Course $course): int
    {
        $classroomId = $course->teacherAssignment()->value('classroom_id');

        if ($classroomId === null) {
            return 0;
        }

        $created = 0;

        User::query()
            ->whereHas('classroomMemberships', fn ($query) => $query
                ->where('classroom_id', $classroomId)
                ->where('status', ClassroomMemberStatus::Active->value))
            ->orderBy('id')
            ->chunkById(200, function ($students) use ($course, &$created): void {
                foreach ($students as $student) {
                    $existed = Enrollment::query()
                        ->where('course_id', $course->getKey())
                        ->where('user_id', $student->getKey())
                        ->exists();

                    $this->enroll($student, $course);

                    if (! $existed) {
                        $created++;
                    }
                }
            });

        return $created;
    }

    /**
     * Drop an enrollment without deleting the record.
     */
    public function drop(Enrollment $enrollment): Enrollment
    {
        $enrollment->update([
            'status' => EnrollmentStatus::Dropped,
            'completed_at' => null,
        ]);

        return $enrollment->refresh();
    }

    public function reactivate(Enrollment $enrollment): Enrollment
    {
        $enrollment->update([
            'status' => EnrollmentStatus::Active,
            'completed_at' => null,
        ]);

        return $enrollment->refresh();
    }

    public function markCompleted(Enrollment $enrollment): Enrollment
    {
        if ($enrollment->status === EnrollmentStatus::Active) {
            $enrollment->update([
                'status' => EnrollmentStatus::Completed,
                'completed_at' => now(),
            ]);
        }

        return $enrollment->refresh();
    }

    /**
     * Whether the student belongs to the classroom that owns the course.
     */
    public function isClassroomMember(User $student, Course $course): bool
    {
        $classroomId = $course->teacherAssignment()->value('classroom_id');

        if ($classroomId === null) {
            return false;
        }

        return $student->classroomMemberships()
            ->where('classroom_id', $classroomId)
            ->where('status', ClassroomMemberStatus::Active->value)
            ->exists();
    }
}
