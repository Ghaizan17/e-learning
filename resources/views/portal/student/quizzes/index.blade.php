@php
    use App\Enums\QuizAttemptStatus;
@endphp

<x-layouts.portal title="Kuis & Ujian">
    <x-ui.page-header title="Kuis & Ujian" description="Kuis dan ujian dari kursus yang Anda ikuti." />

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card padded="false">
        <div class="divide-y divide-slate-100">
            @forelse ($quizzes as $quiz)
                @php($latest = $quiz->attempts->first())
                <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('student.quizzes.show', $quiz) }}"
                               class="text-sm font-semibold text-slate-900 hover:text-indigo-600">{{ $quiz->title }}</a>
                            <x-ui.badge :color="$quiz->type->color()">{{ $quiz->type->label() }}</x-ui.badge>
                            @if ($latest)
                                <x-ui.badge :color="$latest->status->color()">{{ $latest->status->label() }}</x-ui.badge>
                                @if ($latest->score !== null)
                                    <x-ui.badge color="success">{{ $latest->score }} poin</x-ui.badge>
                                @endif
                            @else
                                <x-ui.badge color="slate">Belum dikerjakan</x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $quiz->course?->title }} &middot; {{ $quiz->duration_minutes }} menit
                            &middot; {{ $quiz->questions_count }} soal
                        </p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <p>Maks {{ $quiz->max_attempts }} percobaan</p>
                        @if ($quiz->ends_at)
                            <p class="mt-1">Tutup {{ $quiz->ends_at->translatedFormat('d M Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada kuis" description="Kuis dari kursus Anda akan muncul di sini." />
            @endforelse
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $quizzes->links() }}</div>
</x-layouts.portal>
