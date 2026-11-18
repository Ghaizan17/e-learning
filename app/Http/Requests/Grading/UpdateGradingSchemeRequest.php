<?php

namespace App\Http\Requests\Grading;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradingSchemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course $course */
        $course = $this->route('course');

        return $this->user()?->can('update', $course) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'weights' => ['required', 'array'],
            'weights.assignment' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.quiz' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.exam' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.participation' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
