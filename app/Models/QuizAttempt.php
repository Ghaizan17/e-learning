<?php

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['quiz_id', 'user_id', 'attempt_no', 'started_at', 'expires_at', 'submitted_at', 'status', 'score'])]
class QuizAttempt extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_no' => 'integer',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'status' => QuizAttemptStatus::class,
            'score' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * @param  Builder<QuizAttempt>  $query
     */
    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', QuizAttemptStatus::InProgress);
    }

    public function isExpired(): bool
    {
        return $this->status === QuizAttemptStatus::Expired
            || ($this->status === QuizAttemptStatus::InProgress && $this->expires_at->isPast());
    }

    public function remainingSeconds(): int
    {
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * @return array<int, int>
     */
    public function selectedOptionIdsFor(Question $question): array
    {
        $answer = $this->answers()->where('question_id', $question->getKey())->first();

        if ($answer === null) {
            return [];
        }

        return $answer->selectedOptions()->pluck('question_option_id')->all();
    }

    public function canRevealResult(): bool
    {
        if ($this->status === QuizAttemptStatus::InProgress) {
            return false;
        }

        if ($this->quiz->show_result_immediately) {
            return true;
        }

        return $this->quiz->ends_at !== null && $this->quiz->ends_at->isPast();
    }
}
