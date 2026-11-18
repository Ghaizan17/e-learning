<?php

namespace App\Services\Learning;

use App\Models\Assignment;
use App\Models\Course;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    /**
     * @param  array{title: string, instructions: string, due_at: string, max_score?: float|int, allow_resubmission?: bool}  $data
     */
    public function create(Course $course, array $data): Assignment
    {
        return DB::transaction(fn (): Assignment => $course->assignments()->create([
            'title' => $data['title'],
            'instructions' => $data['instructions'],
            'due_at' => $data['due_at'],
            'max_score' => $data['max_score'] ?? 100,
            'allow_resubmission' => (bool) ($data['allow_resubmission'] ?? false),
            'is_published' => false,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Assignment $assignment, array $data): Assignment
    {
        $assignment->update($data);

        return $assignment->refresh();
    }

    /**
     * Publish an assignment and notify the enrolled students.
     */
    public function publish(Assignment $assignment): Assignment
    {
        if ($assignment->due_at->isPast()) {
            throw ValidationException::withMessages([
                'due_at' => 'Batas waktu tugas sudah lewat. Perbarui tenggat sebelum menerbitkan.',
            ]);
        }

        return DB::transaction(function () use ($assignment): Assignment {
            $assignment->update(['is_published' => true]);

            $students = $assignment->course
                ->enrollments()
                ->active()
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            Notification::send($students, new AssignmentPublishedNotification($assignment));

            return $assignment->refresh();
        });
    }

    public function unpublish(Assignment $assignment): Assignment
    {
        $assignment->update(['is_published' => false]);

        return $assignment->refresh();
    }

    public function delete(Assignment $assignment): void
    {
        DB::transaction(fn () => $assignment->delete());
    }
}
