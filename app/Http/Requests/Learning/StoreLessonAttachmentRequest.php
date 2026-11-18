<?php

namespace App\Http\Requests\Learning;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Foundation\Http\FormRequest;

class StoreLessonAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Lesson $lesson */
        $lesson = $this->route('lesson');

        return $this->user()?->can('create', [LessonAttachment::class, $lesson]) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:150'],
            'file' => [
                'required', 'file', 'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,txt,mp4',
            ],
        ];
    }
}
