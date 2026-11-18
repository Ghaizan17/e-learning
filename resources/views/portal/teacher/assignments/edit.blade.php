<x-layouts.portal :title="'Edit '.$assignment->title">
    <x-ui.page-header :title="$assignment->title" description="Perbarui detail tugas.">
        <x-slot:actions>
            <a href="{{ route('teacher.assignments.show', $assignment) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <form method="POST" action="{{ route('teacher.assignments.update', $assignment) }}">
            @csrf
            @method('PATCH')
            @include('portal.teacher.assignments._form', ['assignment' => $assignment, 'courses' => collect()])

            <div class="mt-6 flex items-center justify-end gap-2">
                <a href="{{ route('teacher.assignments.show', $assignment) }}"
                   class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Simpan perubahan
                </button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.portal>
