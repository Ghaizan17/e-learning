<?php

namespace App\Models;

use App\Enums\QuizType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'course_id', 'title', 'description', 'type', 'duration_minutes',
    'starts_at', 'ends_at', 'max_attempts', 'passing_score',
    'shuffle_questions', 'shuffle_options', 'show_result_immediately', 'is_published',
])]
class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuizType::class,
            'duration_minutes' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'max_attempts' => 'integer',
            'passing_score' => 'decimal:2',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_result_immediately' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * @param  Builder<Quiz>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    public function isOpen(): bool
    {
        return $this->is_published && $this->hasStarted() && ! $this->hasEnded();
    }

    public function totalPoints(): float
    {
        return (float) $this->questions()->sum('score');
    }

    public function latestAttemptFor(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->getKey())
            ->latest('attempt_no')
            ->first();
    }

    public function activeAttemptFor(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->getKey())
            ->where('status', 'in_progress')
            ->first();
    }

    /**
     * @return MorphOne<GradeComponent, $this>
     */
    public function gradeComponent(): MorphOne
    {
        return $this->morphOne(GradeComponent::class, 'gradeable');
    }
}
