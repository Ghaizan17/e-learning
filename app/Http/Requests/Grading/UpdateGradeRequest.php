<?php

namespace App\Http\Requests\Grading;

use App\Models\Grade;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Grade $grade */
        $grade = $this->route('grade');

        return $this->user()?->can('update', $grade) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Grade $grade */
        $grade = $this->route('grade');

        return [
            'score' => ['required', 'numeric', 'min:0', 'max:'.$grade->max_score],
            'reason' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
