<?php

namespace App\Models;

use App\Enums\ClassroomMemberStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['classroom_id', 'user_id', 'joined_at', 'left_at', 'status', 'note'])]
class ClassroomMember extends Model
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
            'joined_at' => 'date',
            'left_at' => 'date',
            'status' => ClassroomMemberStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Classroom, $this>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Limit the query to active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', ClassroomMemberStatus::Active->value);
    }

    /**
     * Whether this membership is still open.
     */
    public function isActive(): bool
    {
        return $this->status === ClassroomMemberStatus::Active && $this->left_at === null;
    }
}
