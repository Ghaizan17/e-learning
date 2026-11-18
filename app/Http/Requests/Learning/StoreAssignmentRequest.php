<?php

namespace App\Http\Requests\Learning;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = Course::query()->find($this->input('course_id'));

        if ($course === null) {
            return false;
        }

        return $this->user()?->can('create', [Assignment::class, $course]) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['required', 'string', 'max:20000'],
            'due_at' => ['required', 'date', 'after:now'],
            'max_score' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'allow_resubmission' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,txt'],
        ];
    }
}
