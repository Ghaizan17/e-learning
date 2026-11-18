<?php

namespace App\Models;

use App\Enums\ClassroomMemberStatus;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'avatar_path', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine whether the user may access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole(RoleName::adminValues());
    }

    /**
     * Avatar URL for the Filament user menu.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        $avatarPath = $this->getRawOriginal('avatar_path');

        return $avatarPath ? Storage::disk('public')->url($avatarPath) : null;
    }

    /**
     * First role name in portal priority order, used to route after login.
     */
    public function primaryRole(): ?RoleName
    {
        return collect(RoleName::cases())
            ->first(fn (RoleName $role): bool => $this->hasRole($role->value));
    }

    /**
     * Whether the user can open the Filament admin panel.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(RoleName::adminValues());
    }

    /**
     * Whether the user can open the teacher portal.
     */
    public function isTeacher(): bool
    {
        return $this->hasAnyRole(RoleName::teacherValues());
    }

    /**
     * Whether the user can open the student portal.
     */
    public function isStudent(): bool
    {
        return $this->hasRole(RoleName::Student->value);
    }

    /**
     * Whether the user acts as a homeroom teacher (wali kelas).
     */
    public function isHomeroomTeacher(): bool
    {
        return $this->hasRole(RoleName::HomeroomTeacher->value);
    }

    /**
     * URL prefix used by the teacher portal for this role.
     */
    public function teacherPortalPrefix(): ?string
    {
        return match (true) {
            $this->isHomeroomTeacher() => 'walikelas',
            $this->isTeacher() => 'guru',
            default => null,
        };
    }

    /**
     * Absolute home URL matching the user's role.
     */
    public function homeUrl(): string
    {
        $route = $this->homeRouteName();
        $prefix = $this->teacherPortalPrefix();

        return $prefix === null
            ? route($route)
            : route($route, ['portal' => $prefix]);
    }

    /**
     * Portal landing route name for the user.
     */
    public function homeRouteName(): string
    {
        return match (true) {
            $this->isAdmin() => 'filament.admin.pages.dashboard',
            $this->isTeacher() => 'teacher.dashboard',
            $this->isStudent() => 'student.dashboard',
            default => 'profile.edit',
        };
    }

    /**
     * @return HasOne<StudentProfile, $this>
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * @return HasOne<TeacherProfile, $this>
     */
    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * Classrooms where this user is the homeroom teacher.
     *
     * @return HasMany<Classroom, $this>
     */
    public function homeroomClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    /**
     * Teaching assignments owned by this user.
     *
     * @return HasMany<TeacherAssignment, $this>
     */
    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    /**
     * Classroom placements (academic history included).
     *
     * @return HasMany<ClassroomMember, $this>
     */
    public function classroomMemberships(): HasMany
    {
        return $this->hasMany(ClassroomMember::class);
    }

    /**
     * Whether this teacher has any assignment in the given classroom.
     */
    public function teachesIn(Classroom $classroom): bool
    {
        return $this->teachingAssignments()->where('classroom_id', $classroom->getKey())->exists();
    }

    /**
     * Whether this user is the homeroom teacher of the given classroom.
     */
    public function isHomeroomOf(Classroom $classroom): bool
    {
        return $classroom->homeroom_teacher_id === $this->getKey();
    }

    /**
     * The student's active classroom in the active academic year, if any.
     */
    public function activeClassroom(): ?Classroom
    {
        return $this->classroomMemberships()
            ->where('status', ClassroomMemberStatus::Active->value)
            ->whereHas('classroom.academicYear', fn ($query) => $query->where('is_active', true))
            ->with('classroom.academicYear')
            ->first()
            ?->classroom;
    }

    /**
     * @return HasMany<Grade, $this>
     */
    public function gradesReceived(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
