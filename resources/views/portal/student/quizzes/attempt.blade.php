@php
    use App\Enums\QuestionType;
@endphp

<x-layouts.portal :title="$quiz->title">
    <div x-data="quizAttempt({
            remaining: {{ $remainingSeconds }},
            saveUrl: '{{ route('student.quiz-attempts.answers', $attempt) }}',
            csrf: '{{ csrf_token() }}'
         })">
        <x-ui.page-header :title="$quiz->title" :description="'Percobaan #'.$attempt->attempt_no.' · '.$quiz->course?->title">
            <x-slot:actions>
                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold"
                      :class="remaining <= 300 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700'">
                    <x-ui.icon name="clock" class="h-4 w-4" />
                    <span x-text="clock()">--:--</span>
                </span>
            </x-slot:actions>
        </x-ui.page-header>

        <div x-show="message" x-cloak class="mb-4">
            <x-ui.alert type="warning"><span x-text="message"></span></x-ui.alert>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
            <span x-show="saving" x-cloak>Menyimpan jawaban…</span>
            <span x-show="!saving && savedAt" x-cloak>Tersimpan <span x-text="savedAt"></span></span>
            <span>Batas waktu server: {{ $attempt->expires_at->translatedFormat('d M Y H:i') }}</span>
        </div>

        <form x-ref="form" method="POST" action="{{ route('student.quiz-attempts.submit', $attempt) }}" class="space-y-4">
            @csrf

            @foreach ($questions as $question)
                @php
                    $answer = $answers->get($question->getKey());
                    $selectedOptionId = $answer?->question_option_id;
                    $selectedOptionIds = $answer?->selectedOptions->pluck('question_option_id')->all() ?? [];
                @endphp

                <x-ui.card :title="'Soal '.$question->position">
                    <div id="question-{{ $question->getKey() }}">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span>{{ $question->score }} poin</span>
                            @if ($question->type === QuestionType::MultipleSelect)
                                <span>&middot; pilih semua jawaban yang benar</span>
                            @endif
                        </div>

                        <p class="mt-2 whitespace-pre-line text-sm font-medium text-slate-800">{{ $question->question_text }}</p>

                        <div class="mt-3 space-y-2">
                            @if ($question->type === QuestionType::MultipleChoice || $question->type === QuestionType::TrueFalse)
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                                        <input type="radio" name="answers[{{ $question->getKey() }}]" value="{{ $option->getKey() }}"
                                               @checked((int) $selectedOptionId === $option->getKey())
                                               @change="save({{ $question->getKey() }})"
                                               class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-600">
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            @elseif ($question->type === QuestionType::MultipleSelect)
                                @foreach ($question->options as $option)
                                    <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                                        <input type="checkbox" name="answers[{{ $question->getKey() }}][]" value="{{ $option->getKey() }}"
                                               @checked(in_array($option->getKey(), $selectedOptionIds, true))
                                               @change="save({{ $question->getKey() }})"
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600">
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            @elseif ($question->type === QuestionType::ShortAnswer)
                                <input type="text" name="answers_text[{{ $question->getKey() }}]"
                                       value="{{ $answer?->answer_text }}" maxlength="1000"
                                       @change="save({{ $question->getKey() }})"
                                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @else
                                <textarea name="answers_text[{{ $question->getKey() }}]" rows="6" maxlength="10000"
                                          @change="save({{ $question->getKey() }})"
                                          class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $answer?->answer_text }}</textarea>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
            @endforeach

            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('student.quizzes.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Keluar tanpa mengumpulkan</a>
                <button type="submit" :disabled="remaining <= 0"
                        onclick="return confirm('Kumpulkan jawaban sekarang? Jawaban tidak dapat diubah setelah dikumpulkan.')"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    <x-ui.icon name="check" class="h-4 w-4" />
                    Kumpulkan jawaban
                </button>
            </div>
        </form>
    </div>
</x-layouts.portal>
