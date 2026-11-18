<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_answer_id', 'question_option_id'])]
class QuizAnswerOption extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<QuizAnswer, $this>
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(QuizAnswer::class, 'quiz_answer_id');
    }

    /**
     * @return BelongsTo<QuestionOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
