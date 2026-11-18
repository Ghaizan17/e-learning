<?php

namespace App\Models;

use App\Enums\GradingCategoryName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'name', 'weight'])]
class GradingCategory extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => GradingCategoryName::class,
            'weight' => 'decimal:2',
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
     * @return HasMany<GradeComponent, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(GradeComponent::class);
    }
}
