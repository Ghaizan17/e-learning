<x-layouts.portal :title="'Edit '.$quiz->title">
    <x-ui.page-header :title="$quiz->title" description="Perbarui pengaturan kuis.">
        <x-slot:actions>
            <a href="{{ route('teacher.quizzes.show', $quiz) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form method="POST" action="{{ route('teacher.quizzes.update', $quiz) }}">
            @csrf
            @method('PATCH')
            @include('portal.teacher.quizzes._form', ['quiz' => $quiz, 'courses' => collect()])

            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('teacher.quizzes.show', $quiz) }}"
                   class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.portal>
