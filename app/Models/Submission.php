<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['assignment_id', 'user_id', 'attempt_no', 'is_latest', 'content', 'status', 'submitted_at'])]
class Submission extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_no' => 'integer',
            'is_latest' => 'boolean',
            'status' => SubmissionStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Assignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<SubmissionFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    /**
     * @return HasOne<SubmissionGrade, $this>
     */
    public function grade(): HasOne
    {
        return $this->hasOne(SubmissionGrade::class);
    }

    /**
     * @param  Builder<Submission>  $query
     */
    public function scopeLatest(Builder $query): void
    {
        $query->where('is_latest', true);
    }

    public function isGraded(): bool
    {
        return $this->status === SubmissionStatus::Graded;
    }
}
