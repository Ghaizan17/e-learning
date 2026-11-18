<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['course_id', 'title', 'instructions', 'due_at', 'max_score', 'allow_resubmission', 'is_published'])]
class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'max_score' => 'decimal:2',
            'allow_resubmission' => 'boolean',
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
     * @return HasMany<AssignmentAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }

    /**
     * @return HasMany<Submission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * @param  Builder<Assignment>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function isOverdue(): bool
    {
        return $this->due_at->isPast();
    }

    public function latestSubmissionFor(User $user): ?Submission
    {
        return $this->submissions()
            ->where('user_id', $user->getKey())
            ->where('is_latest', true)
            ->with(['files', 'grade'])
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
