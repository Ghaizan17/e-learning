<?php

namespace App\Http\Requests\Learning;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentAttachmentRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:150'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,txt'],
        ];
    }
}
