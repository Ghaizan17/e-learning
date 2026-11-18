@php
    $question = $question ?? null;

    $options = collect(old('options', $question?->options->map(fn ($option): array => [
        'id' => $option->getKey(),
        'option_text' => $option->option_text,
        'is_correct' => $option->is_correct,
    ])->all() ?? []));

    while ($options->count() < 2) {
        $options->push(['id' => null, 'option_text' => '', 'is_correct' => false]);
    }

    $foundCorrect = $options->search(fn (array $option): bool => (bool) ($option['is_correct'] ?? false));
    $correctIndex = $foundCorrect === false ? -1 : (int) $foundCorrect;
    $correctIndexes = $options->filter(fn (array $option): bool => (bool) ($option['is_correct'] ?? false))->keys()->all();
    $maxRows = max(2, $options->count());
@endphp

<div x-data="{ type: '{{ old('type', $question?->type?->value ?? 'multiple_choice') }}' }" class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-sm font-medium text-slate-700">Tipe soal</label>
            <select name="type" x-model="type" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                @foreach (App\Enums\QuestionType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Poin</label>
            <input name="score" type="number" min="0.5" max="100" step="0.5" required
                   value="{{ old('score', $question?->score ?? 10) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="sm:col-span-1">
            <label class="block text-sm font-medium text-slate-700">Penjelasan (opsional)</label>
            <input name="explanation" type="text" maxlength="2000" value="{{ old('explanation', $question?->explanation) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Pertanyaan</label>
        <textarea name="question_text" rows="3" required maxlength="10000"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('question_text', $question?->question_text) }}</textarea>
    </div>

    <div x-show="type !== 'essay'" class="rounded-lg border border-slate-200 bg-slate-50 p-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pilihan jawaban</p>
        <p class="mt-1 text-xs text-slate-500">
            Tandai jawaban benar. Untuk jawaban singkat, tulis jawaban yang diterima pada kolom pilihan.
        </p>

        <div class="mt-3 space-y-2">
            @for ($index = 0; $index < $maxRows; $index++)
                @php($option = $options[$index] ?? ['id' => null, 'option_text' => '', 'is_correct' => false])
                <div class="flex items-center gap-2">
                    <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option['id'] ?? '' }}">
                    <input name="options[{{ $index }}][option_text]" type="text" maxlength="1000"
                           value="{{ $option['option_text'] ?? '' }}" placeholder="Pilihan {{ $index + 1 }}"
                           class="block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    <label class="inline-flex shrink-0 items-center gap-1 text-xs text-slate-600">
                        <input x-show="type !== 'multiple_select'" type="radio" name="correct" value="{{ $index }}"
                               @checked($correctIndex === $index)
                               class="h-4 w-4 border-slate-300 text-emerald-600">
                        <input x-show="type === 'multiple_select'" type="checkbox" name="correct_options[]" value="{{ $index }}"
                               @checked(in_array($index, $correctIndexes, true))
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600">
                        Benar
                    </label>
                </div>
            @endfor
        </div>
    </div>
</div>
