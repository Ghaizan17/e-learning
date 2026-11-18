<?php

namespace App\Http\Requests\Learning;

use App\Models\QuizAttempt;
use Illuminate\Foundation\Http\FormRequest;

class SaveQuizAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var QuizAttempt $attempt */
        $attempt = $this->route('attempt');

        return $this->user()?->can('submit', $attempt) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'question_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer', 'exists:question_options,id'],
            'answer_text' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
