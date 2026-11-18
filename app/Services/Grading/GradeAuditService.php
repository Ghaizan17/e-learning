<?php

namespace App\Services\Grading;

use App\Models\Grade;
use App\Models\GradeHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeAuditService
{
    public function __construct(private readonly GradeCalculationService $calculation) {}

    /**
     * Update a grade and record the change; every manual edit leaves an audit trail.
     *
     * @param  array{score: float|int|string, reason?: ?string, is_published?: bool}  $data
     */
    public function updateGrade(Grade $grade, User $editor, array $data): Grade
    {
        $score = (float) $data['score'];

        if ($score < 0 || $score > (float) $grade->max_score) {
            throw ValidationException::withMessages([
                'score' => 'Skor harus berada di antara 0 dan '.$grade->max_score.'.',
            ]);
        }

        return DB::transaction(function () use ($grade, $editor, $data, $score): Grade {
            /** @var Grade $locked */
            $locked = Grade::query()->whereKey($grade->getKey())->lockForUpdate()->firstOrFail();
            $oldScore = (float) $locked->score;

            $locked->update([
                'score' => $score,
                'percentage' => round($score / max((float) $locked->max_score, 0.01) * 100, 2),
                'graded_by' => $editor->getKey(),
                'is_published' => array_key_exists('is_published', $data)
                    ? (bool) $data['is_published']
                    : $locked->is_published,
            ]);

            if (abs($oldScore - $score) > 0.001) {
                GradeHistory::query()->create([
                    'grade_id' => $locked->getKey(),
                    'old_score' => $oldScore,
                    'new_score' => $score,
                    'changed_by' => $editor->getKey(),
                    'reason' => $data['reason'] ?? null,
                ]);
            }

            $this->calculation->recalculateForUser($locked->course, $locked->student);

            return $locked->refresh();
        });
    }

    /**
     * Publish or hide a grade from the student portal.
     */
    public function setPublished(Grade $grade, User $editor, bool $published): Grade
    {
        $grade->update([
            'is_published' => $published,
            'graded_by' => $editor->getKey(),
        ]);

        $this->calculation->recalculateForUser($grade->course, $grade->student);

        return $grade->refresh();
    }
}
