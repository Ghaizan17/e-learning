@php
    use App\Enums\QuestionType;

    $totalPoints = (float) $questions->sum(fn ($question) => (float) $question->score);
    $score = (float) ($attempt->score ?? 0);
    $passed = $score >= (float) $quiz->passing_score;
    $percent = $totalPoints > 0 ? round($score / $totalPoints * 100) : 0;
@endphp

<x-layouts.portal :title="'Hasil '.$quiz->title">
    <x-ui.page-header :title="$quiz->title" :description="'Hasil percobaan #'.$attempt->attempt_no">
        <x-slot:actions>
            <x-ui.badge :color="$attempt->status->color()">{{ $attempt->status->label() }}</x-ui.badge>
            <a href="{{ route('student.quizzes.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Daftar kuis
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
        <x-ui.stat label="Skor" :value="$score.' / '.$totalPoints" :hint="$percent.'%'" />
        <x-ui.stat label="Nilai kelulusan" :value="$quiz->passing_score" />
        <x-ui.stat label="Status" :value="$attempt->status->label()" />
        <x-ui.stat label="Dikumpulkan" :value="$attempt->submitted_at?->translatedFormat('d M Y H:i') ?? '—'" />
    </div>

    <x-ui.alert :type="$passed ? 'success' : 'warning'" class="mt-4">
        {{ $passed ? 'Selamat, Anda lulus kuis ini.' : 'Belum mencapai nilai kelulusan. Pelajari kembali materi terkait.' }}
    </x-ui.alert>

    <div class="mt-6 space-y-3">
        @foreach ($questions as $question)
            @php($answer = $answers->get($question->getKey()))
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-semibold text-slate-400">Soal {{ $question->position }}</span>
                            @if ($answer?->is_correct === true)
                                <x-ui.badge color="success">Benar</x-ui.badge>
                            @elseif ($answer?->is_correct === false)
                                <x-ui.badge color="danger">Salah</x-ui.badge>
                            @elseif ($question->type === QuestionType::Essay)
                                <x-ui.badge :color="$answer?->score_awarded !== null ? 'success' : 'warning'">
                                    {{ $answer?->score_awarded !== null ? 'Dinilai' : 'Menunggu penilaian' }}
                                </x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-800">{{ $question->question_text }}</p>
                    </div>
                    <span class="text-xs text-slate-500">{{ $answer?->score_awarded ?? '—' }} / {{ $question->score }}</span>
                </div>

                <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jawaban Anda</p>
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
                    </div>

                    @if ($question->type->requiresOptions())
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Jawaban benar</p>
                            <p class="mt-1 text-emerald-900">{{ $question->options->where('is_correct', true)->pluck('option_text')->implode(', ') }}</p>
                        </div>
                    @endif
                </div>

                @if ($question->explanation)
                    <p class="mt-2 text-xs text-slate-500">Penjelasan: {{ $question->explanation }}</p>
                @endif
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.portal>
