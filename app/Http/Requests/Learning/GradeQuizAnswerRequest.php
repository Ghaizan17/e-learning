<?php

namespace App\Http\Requests\Learning;

use App\Models\QuizAnswer;
use Illuminate\Foundation\Http\FormRequest;

class GradeQuizAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var QuizAnswer $answer */
        $answer = $this->route('answer');

        return $this->user()?->can('grade', $answer) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'score' => ['required', 'numeric', 'min:0'],
        ];
    }
}
