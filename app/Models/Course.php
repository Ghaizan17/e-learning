<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['teacher_assignment_id', 'title', 'slug', 'description', 'thumbnail_path', 'status', 'published_at'])]
class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TeacherAssignment, $this>
     */
    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    /**
     * @return HasMany<Chapter, $this>
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    /**
     * Every lesson in the course, regardless of publication state.
     *
     * @return HasManyThrough<Lesson, Chapter, $this>
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class)->orderBy('chapters.position')->orderBy('lessons.position');
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['enrolled_at', 'completed_at', 'status', 'final_score', 'letter_grade'])
            ->withTimestamps();
    }

    /**
     * Lessons that are visible to students (published chapter and lesson).
     *
     * @return HasManyThrough<Lesson, Chapter, $this>
     */
    public function publishedLessons(): HasManyThrough
    {
        return $this->lessons()
            ->where('chapters.is_published', true)
            ->where('lessons.is_published', true);
    }

    /**
     * @param  Builder<Course>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', CourseStatus::Published->value);
    }

    /**
     * @param  Builder<Course>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->whereHas('enrollments', fn (Builder $enrollments) => $enrollments->where('user_id', $user->getKey()))
                ->orWhereHas('teacherAssignment', fn (Builder $assignment) => $assignment->where('teacher_id', $user->getKey()));
        });
    }

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->teacherAssignment?->teacher_id === $user->getKey();
    }
}
