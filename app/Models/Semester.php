<?php

namespace App\Models;

use App\Enums\SemesterName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['academic_year_id', 'name', 'sequence', 'start_date', 'end_date', 'is_active'])]
class Semester extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => SemesterName::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'sequence' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Semester $semester): void {
            if ($semester->is_active) {
                static::query()
                    ->whereKeyNot($semester->getKey())
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return HasMany<TeacherAssignment, $this>
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * Limit the query to the active semester.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
