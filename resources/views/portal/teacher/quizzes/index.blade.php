<x-layouts.portal title="Kuis & Ujian">
    <x-ui.page-header title="Kuis & Ujian" description="Kelola kuis, ujian, dan percobaan siswa.">
        <x-slot:actions>
            <a href="{{ route('teacher.quizzes.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Buat kuis
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card padded="false">
        <div class="divide-y divide-slate-100">
            @forelse ($quizzes as $quiz)
                <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('teacher.quizzes.show', $quiz) }}"
                               class="text-sm font-semibold text-slate-900 hover:text-indigo-600">{{ $quiz->title }}</a>
                            <x-ui.badge :color="$quiz->type->color()">{{ $quiz->type->label() }}</x-ui.badge>
                            <x-ui.badge :color="$quiz->is_published ? 'success' : 'slate'">
                                {{ $quiz->is_published ? 'Terbit' : 'Draf' }}
                            </x-ui.badge>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $quiz->course?->title }} &middot; {{ $quiz->duration_minutes }} menit
                            &middot; {{ $quiz->questions_count }} soal &middot; {{ $quiz->attempts_count }} percobaan
                        </p>
                    </div>
                    <a href="{{ route('teacher.quizzes.show', $quiz) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Kelola</a>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada kuis" description="Buat kuis atau ujian pertama untuk kursus Anda." />
            @endforelse
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $quizzes->links() }}</div>
</x-layouts.portal>
