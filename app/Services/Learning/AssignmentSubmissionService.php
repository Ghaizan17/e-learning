<?php

namespace App\Services\Learning;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\SubmissionGrade;
use App\Models\User;
use App\Notifications\AssignmentGradedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentSubmissionService
{
    public function __construct(private readonly CourseAccessService $access) {}

    /**
     * Submit an assignment, flagging late work and closing the previous attempt.
     *
     * @param  array{content?: ?string}  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function submit(Assignment $assignment, User $student, array $data, array $files = []): Submission
    {
        if (! $assignment->is_published) {
            throw ValidationException::withMessages([
                'content' => 'Tugas ini belum diterbitkan.',
            ]);
        }

        if (! $this->access->isEnrolled($student, $assignment->course)) {
            throw ValidationException::withMessages([
                'content' => 'Anda tidak terdaftar pada kursus ini.',
            ]);
        }

        $previous = $this->latestFor($assignment, $student);

        if ($previous !== null && ! $assignment->allow_resubmission) {
            throw ValidationException::withMessages([
                'content' => 'Tugas ini tidak mengizinkan pengumpulan ulang.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $student, $data, $files, $previous): Submission {
            if ($previous !== null) {
                $previous->update(['is_latest' => false]);
            }

            $attempt = $previous !== null ? $previous->attempt_no + 1 : 1;

            $submission = $assignment->submissions()->create([
                'user_id' => $student->getKey(),
                'attempt_no' => $attempt,
                'is_latest' => true,
                'content' => $data['content'] ?? null,
                'status' => $assignment->isOverdue() ? SubmissionStatus::Late : SubmissionStatus::Submitted,
                'submitted_at' => now(),
            ]);

            foreach ($files as $file) {
                $path = $file->store('submissions/'.$assignment->getKey().'/'.$student->getKey(), 'local');

                $submission->files()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }

            return $submission->load(['files', 'grade']);
        });
    }

    public function latestFor(Assignment $assignment, User $student): ?Submission
    {
        return $assignment->submissions()
            ->where('user_id', $student->getKey())
            ->where('is_latest', true)
            ->with(['files', 'grade'])
            ->first();
    }

    /**
     * Grade the latest submission and notify the student.
     */
    public function grade(Submission $submission, User $grader, float $score, ?string $feedback): SubmissionGrade
    {
        $assignment = $submission->assignment;

        if ($score < 0 || $score > (float) $assignment->max_score) {
            throw ValidationException::withMessages([
                'score' => 'Skor harus berada di antara 0 dan '.$assignment->max_score.'.',
            ]);
        }

        return DB::transaction(function () use ($submission, $grader, $score, $feedback): SubmissionGrade {
            $grade = SubmissionGrade::query()->updateOrCreate(
                ['submission_id' => $submission->getKey()],
                [
                    'graded_by' => $grader->getKey(),
                    'score' => $score,
                    'feedback' => $feedback,
                    'graded_at' => now(),
                ],
            );

            $submission->update(['status' => SubmissionStatus::Graded]);

            $student = $submission->student;

            if ($student !== null) {
                $student->notify(new AssignmentGradedNotification($submission->refresh()->load('grade')));
            }

            return $grade;
        });
    }

    /**
     * Whether the student may submit a new attempt right now.
     */
    public function canSubmit(Assignment $assignment, User $student): bool
    {
        if (! $assignment->is_published || ! $this->access->isEnrolled($student, $assignment->course)) {
            return false;
        }

        $latest = $this->latestFor($assignment, $student);

        return $latest === null || $assignment->allow_resubmission;
    }
}
