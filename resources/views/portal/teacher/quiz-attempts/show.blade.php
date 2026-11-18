@php
    use App\Enums\QuestionType;
@endphp

<x-layouts.portal :title="'Percobaan '.$attempt->student?->name">
    <x-ui.page-header :title="$attempt->quiz->title"
                      :description="$attempt->student?->name.' · percobaan #'.$attempt->attempt_no">
        <x-slot:actions>
            <x-ui.badge :color="$attempt->status->color()">{{ $attempt->status->label() }}</x-ui.badge>
            <a href="{{ route('teacher.quizzes.show', $attempt->quiz) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Skor" :value="$attempt->score ?? '—'" :hint="'dari '.$attempt->quiz->questions->sum(fn ($question) => (float) $question->score).' poin'" />
        <x-ui.stat label="Mulai" :value="$attempt->started_at->translatedFormat('d M H:i')" />
        <x-ui.stat label="Batas waktu" :value="$attempt->expires_at->translatedFormat('d M H:i')" />
        <x-ui.stat label="Dikumpulkan" :value="$attempt->submitted_at?->translatedFormat('d M H:i') ?? '—'" />
    </div>

    <div class="mt-6 space-y-3">
        @foreach ($questions as $question)
            @php($answer = $answers->get($question->getKey()))
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-slate-400">Soal {{ $question->position }}</span>
                            <x-ui.badge color="slate">{{ $question->type->label() }}</x-ui.badge>
                            @if ($answer?->is_correct === true)
                                <x-ui.badge color="success">Benar</x-ui.badge>
                            @elseif ($answer?->is_correct === false)
                                <x-ui.badge color="danger">Salah</x-ui.badge>
                            @elseif ($question->type === QuestionType::Essay)
                                <x-ui.badge :color="$answer?->score_awarded !== null ? 'success' : 'warning'">
                                    {{ $answer?->score_awarded !== null ? 'Sudah dinilai' : 'Menunggu nilai' }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $question->question_text }}</p>
                    </div>
                    <p class="text-xs text-slate-500">{{ $answer?->score_awarded ?? '—' }} / {{ $question->score }}</p>
                </div>

                <div class="mt-3 rounded-lg bg-slate-50 p-3 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jawaban siswa</p>
                    @if ($question->type === QuestionType::MultipleSelect)
                        <ul class="mt-1 list-inside list-disc text-slate-700">
                            @forelse ($answer?->selectedOptions ?? [] as $selected)
                                <li>{{ $question->options->firstWhere('id', $selected->question_option_id)?->option_text }}</li>
                            @empty
                                <li class="text-slate-400">Tidak dijawab</li>
                            @endforelse
                        </ul>
                    @elseif ($question->type->requiresOptions())
                        <p class="mt-1 text-slate-700">{{ $answer?->selectedOption?->option_text ?? 'Tidak dijawab' }}</p>
                    @else
                        <p class="mt-1 whitespace-pre-line text-slate-700">{{ $answer?->answer_text ?: 'Tidak dijawab' }}</p>
                    @endif

                    @if ($question->type->requiresOptions())
                        <p class="mt-2 text-xs text-slate-500">
                            Jawaban benar:
                            {{ $question->options->where('is_correct', true)->pluck('option_text')->implode(', ') }}
                        </p>
                    @endif
                </div>

                @if ($question->type === QuestionType::Essay && $answer)
                    <form method="POST" action="{{ route('teacher.quiz-answers.grade', $answer) }}"
                          class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-600">Skor (0–{{ $question->score }})</label>
                            <input name="score" type="number" min="0" max="{{ $question->score }}" step="0.5"
                                   value="{{ $answer?->score_awarded }}" required
                                   class="mt-1 w-28 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                            Simpan nilai
                        </button>
                    </form>
                @endif
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.portal>
