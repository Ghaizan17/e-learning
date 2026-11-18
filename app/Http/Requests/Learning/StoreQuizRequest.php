<?php

namespace App\Http\Requests\Learning;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = Course::query()->find($this->input('course_id'));

        return $course !== null && ($this->user()?->can('create', [Quiz::class, $course]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'in:quiz,exam'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'show_result_immediately' => ['nullable', 'boolean'],
        ];
    }
}
