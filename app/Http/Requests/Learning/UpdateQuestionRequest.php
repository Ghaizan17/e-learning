<?php

namespace App\Http\Requests\Learning;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Question $question */
        $question = $this->route('question');

        return $this->user()?->can('update', $question) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return (new StoreQuestionRequest)->rules();
    }
}
