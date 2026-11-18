<?php

namespace App\Http\Requests\Learning;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Assignment $assignment */
        $assignment = $this->route('assignment');

        return $this->user()?->can('update', $assignment) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['required', 'string', 'max:20000'],
            'due_at' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'allow_resubmission' => ['nullable', 'boolean'],
        ];
    }
}
