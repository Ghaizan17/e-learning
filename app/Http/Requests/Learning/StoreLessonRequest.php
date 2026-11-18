<?php

namespace App\Http\Requests\Learning;

use App\Enums\LessonType;
use App\Models\Chapter;
use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Chapter $chapter */
        $chapter = $this->route('chapter');

        return $this->user()?->can('create', [Lesson::class, $chapter]) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'type' => ['required', new Enum(LessonType::class)],
            'content_html' => ['nullable', 'string', 'max:200000'],
            'video_url' => ['nullable', 'required_if:type,video', 'url', 'max:2048'],
            'external_url' => ['nullable', 'required_if:type,link', 'url', 'max:2048'],
            'code_language' => ['nullable', 'string', Rule::in(['php', 'javascript', 'html', 'css', 'sql', 'python', 'java', 'bash'])],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
        ];
    }
}
