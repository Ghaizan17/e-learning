<?php

namespace App\Http\Requests\Learning;

use App\Models\Chapter;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Chapter $chapter */
        $chapter = $this->route('chapter');

        return $this->user()?->can('update', $chapter) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
