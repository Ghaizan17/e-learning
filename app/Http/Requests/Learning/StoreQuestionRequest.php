<?php

namespace App\Http\Requests\Learning;

use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Quiz $quiz */
        $quiz = $this->route('quiz');

        return $this->user()?->can('update', $quiz) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:multiple_choice,multiple_select,true_false,short_answer,essay'],
            'question_text' => ['required', 'string', 'max:10000'],
            'score' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'explanation' => ['nullable', 'string', 'max:2000'],
            'options' => ['nullable', 'array', 'max:10'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.option_text' => ['nullable', 'string', 'max:1000'],
            'correct' => ['nullable', 'integer', 'min:0', 'max:9'],
            'correct_options' => ['nullable', 'array'],
            'correct_options.*' => ['integer', 'min:0', 'max:9'],
        ];
    }
}
