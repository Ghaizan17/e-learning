<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
