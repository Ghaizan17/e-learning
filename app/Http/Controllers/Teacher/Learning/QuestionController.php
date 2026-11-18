<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Enums\QuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreQuestionRequest;
use App\Http\Requests\Learning\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\Learning\QuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    public function __construct(private readonly QuestionService $questions) {}

    public function store(StoreQuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        try {
            $this->questions->create($quiz, $this->payload($request));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'Soal ditambahkan.');
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        try {
            $this->questions->update($question, $this->payload($request));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'Soal diperbarui.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);

        $this->questions->delete($question);

        return back()->with('status', 'Soal dihapus.');
    }

    /**
     * Convert the builder form input into the service payload.
     *
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $type = QuestionType::from((string) $request->input('type'));

        $rawOptions = collect($request->input('options', []))
            ->filter(fn ($option): bool => is_array($option) && filled($option['option_text'] ?? null))
            ->values();

        $multiple = $type === QuestionType::MultipleSelect;

        $correct = $multiple
            ? array_map('intval', $request->input('correct_options', []))
            : [(int) $request->input('correct', -1)];

        $options = $type->requiresOptions()
            ? $rawOptions->map(fn (array $option, int $index): array => [
                'id' => isset($option['id']) ? (int) $option['id'] : null,
                'option_text' => (string) $option['option_text'],
                'is_correct' => in_array($index, $correct, true),
            ])->all()
            : [];

        return [
            'type' => $type->value,
            'question_text' => (string) $request->input('question_text'),
            'score' => (float) $request->input('score', 1),
            'explanation' => $request->input('explanation'),
            'options' => $options,
        ];
    }
}
