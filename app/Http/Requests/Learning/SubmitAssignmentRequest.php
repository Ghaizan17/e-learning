<?php

namespace App\Http\Requests\Learning;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Assignment $assignment */
        $assignment = $this->route('assignment');

        return $this->user()?->can('create', [Submission::class, $assignment]) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:20000', 'required_without:files'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg,txt'],
        ];
    }
}
