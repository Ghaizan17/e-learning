<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id', 'user_id', 'grade_component_id', 'score', 'max_score',
    'percentage', 'graded_by', 'is_published',
])]
class Grade extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'percentage' => 'decimal:2',
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
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * @return BelongsTo<GradeComponent, $this>
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class, 'grade_component_id');
    }

    /**
     * @return HasMany<GradeHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(GradeHistory::class)->latest();
    }

    /**
     * @param  Builder<Grade>  $query
     */
    public function scopePublished($query): void
    {
        $query->where('is_published', true);
    }
}
