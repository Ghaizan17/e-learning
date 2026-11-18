<?php

namespace App\Services\Learning;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Quiz $quiz, array $data): Question
    {
        $this->assertOptions($data);

        return DB::transaction(function () use ($quiz, $data): Question {
            $question = $quiz->questions()->create([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'score' => $data['score'] ?? 1,
                'explanation' => $data['explanation'] ?? null,
                'position' => $this->nextPosition($quiz),
            ]);

            $this->syncOptions($question, $data['options'] ?? []);

            return $question->load('options');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Question $question, array $data): Question
    {
        $this->assertOptions($data);

        return DB::transaction(function () use ($question, $data): Question {
            $question->update([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'score' => $data['score'] ?? $question->score,
                'explanation' => $data['explanation'] ?? null,
            ]);

            $this->syncOptions($question, $data['options'] ?? []);

            return $question->refresh()->load('options');
        });
    }

    public function delete(Question $question): void
    {
        DB::transaction(fn () => $question->delete());
    }

    public function nextPosition(Quiz $quiz): int
    {
        return (int) $quiz->questions()->withTrashed()->max('position') + 1;
    }

    /**
     * @param  array<int, array{id?: int|null, option_text: string, is_correct?: bool}>  $options
     */
    private function syncOptions(Question $question, array $options): void
    {
        $keptIds = [];

        foreach (array_values($options) as $index => $option) {
            $attributes = [
                'option_text' => $option['option_text'],
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'position' => $index + 1,
            ];

            $id = $option['id'] ?? null;

            if ($id !== null && $question->options()->whereKey($id)->exists()) {
                QuestionOption::query()->whereKey($id)->update($attributes);
                $keptIds[] = $id;

                continue;
            }

            $keptIds[] = $question->options()->create($attributes)->getKey();
        }

        $question->options()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertOptions(array $data): void
    {
        $type = QuestionType::from($data['type']);
        $options = array_values($data['options'] ?? []);

        if (! $type->requiresOptions()) {
            return;
        }

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => 'Soal ini membutuhkan minimal dua pilihan jawaban.',
            ]);
        }

        $correct = collect($options)->filter(fn (array $option): bool => (bool) ($option['is_correct'] ?? false))->count();

        if ($correct === 0) {
            throw ValidationException::withMessages([
                'options' => 'Tandai minimal satu jawaban benar.',
            ]);
        }

        if ($type !== QuestionType::MultipleSelect && $correct > 1) {
            throw ValidationException::withMessages([
                'options' => 'Soal ini hanya boleh memiliki satu jawaban benar.',
            ]);
        }
    }
}
