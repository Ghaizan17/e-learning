@php
    use App\Enums\QuizAttemptStatus;
@endphp

<x-layouts.portal :title="$quiz->title">
    <x-ui.page-header :title="$quiz->title" :description="$quiz->course?->title.' · '.$quiz->course?->teacherAssignment?->subject?->name">
        <x-slot:actions>
            <x-ui.badge :color="$quiz->type->color()">{{ $quiz->type->label() }}</x-ui.badge>
            <a href="{{ route('student.quizzes.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Daftar
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
        <x-ui.stat label="Durasi" :value="$quiz->duration_minutes.' menit'" />
        <x-ui.stat label="Jumlah soal" :value="$quiz->questions()->count()" />
        <x-ui.stat label="Percobaan" :value="$usedAttempts.' / '.$quiz->max_attempts" />
        <x-ui.stat label="Nilai kelulusan" :value="$quiz->passing_score" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Petunjuk">
            @if ($quiz->description)
                <p class="whitespace-pre-line text-sm text-slate-700">{{ $quiz->description }}</p>
            @endif
            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-slate-600">
                <li>Waktu berjalan sejak Anda menekan mulai dan tidak bisa dijeda.</li>
                <li>Jawaban tersimpan otomatis; kumpulkan sebelum waktu habis.</li>
                <li>Setelah waktu habis jawaban tidak dapat dikirim.</li>
                <li>Jendela kuis: {{ $quiz->starts_at?->translatedFormat('d M Y H:i') ?? 'sekarang' }} — {{ $quiz->ends_at?->translatedFormat('d M Y H:i') ?? 'tanpa batas' }}.</li>
            </ul>
        </x-ui.card>

        <x-ui.card title="Status">
            @if ($activeAttempt)
                <p class="text-sm text-slate-700">Anda memiliki percobaan yang belum selesai.</p>
                <a href="{{ route('student.quiz-attempts.show', $activeAttempt) }}"
                   class="mt-3 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    <x-ui.icon name="play" class="h-4 w-4" />
                    Lanjutkan
                </a>
            @elseif ($canStart)
                <p class="text-sm text-slate-700">Anda dapat memulai percobaan baru sekarang.</p>
                <form method="POST" action="{{ route('student.quizzes.start', $quiz) }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <x-ui.icon name="play" class="h-4 w-4" />
                        Mulai {{ $quiz->type->label() }}
                    </button>
                </form>
            @else
                <p class="text-sm text-slate-700">Tidak ada percobaan tersisa atau kuis sedang ditutup.</p>
            @endif

            @if ($latestAttempt && $latestAttempt->status !== QuizAttemptStatus::InProgress)
                <div class="mt-4 border-t border-slate-100 pt-3 text-sm">
                    <p class="text-slate-600">Percobaan terakhir: <span class="font-semibold">{{ $latestAttempt->status->label() }}</span></p>
                    @if ($latestAttempt->score !== null && $latestAttempt->canRevealResult())
                        <p class="mt-1 text-slate-600">Skor: <span class="font-semibold">{{ $latestAttempt->score }}</span></p>
                        <a href="{{ route('student.quiz-attempts.result', $latestAttempt) }}"
                           class="mt-2 inline-block text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat hasil</a>
                    @elseif ($latestAttempt->score !== null)
                        <p class="mt-1 text-xs text-slate-500">Hasil tersedia setelah kuis berakhir.</p>
                    @endif
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.portal>
