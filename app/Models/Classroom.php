<?php

namespace App\Models;

use App\Enums\ClassroomMemberStatus;
use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['academic_year_id', 'department_id', 'homeroom_teacher_id', 'name', 'grade_level', 'capacity'])]
class Classroom extends Model
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
            'grade_level' => GradeLevel::class,
            'capacity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }

    /**
     * @return HasMany<ClassroomMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ClassroomMember::class);
    }

    /**
     * @return HasMany<ClassroomMember, $this>
     */
    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', ClassroomMemberStatus::Active->value);
    }

    /**
     * Students ever placed in this classroom (history included).
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_members')
            ->withPivot(['joined_at', 'left_at', 'status', 'note'])
            ->withTimestamps();
    }

    /**
     * Students currently placed in this classroom.
     *
     * @return BelongsToMany<User, $this>
     */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('status', ClassroomMemberStatus::Active->value);
    }

    /**
     * @return HasMany<TeacherAssignment, $this>
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }
}
