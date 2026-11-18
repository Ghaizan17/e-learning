<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['course_id', 'grading_category_id', 'gradeable_type', 'gradeable_id', 'title', 'max_score'])]
class GradeComponent extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
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
     * @return BelongsTo<GradingCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(GradingCategory::class, 'grading_category_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function gradeable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<Grade, $this>
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
