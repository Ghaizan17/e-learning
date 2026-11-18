<x-layouts.portal title="Buat Tugas">
    <x-ui.page-header title="Buat tugas" description="Tugas disimpan sebagai draf sampai Anda menerbitkannya.">
        <x-slot:actions>
            <a href="{{ route('teacher.assignments.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($courses->isEmpty())
        <x-ui.empty-state title="Belum ada kursus"
                          description="Anda perlu membuat kursus sebelum menambahkan tugas." />
    @else
        <x-ui.card>
            <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data">
                @csrf
                @include('portal.teacher.assignments._form', ['assignment' => null, 'courses' => $courses])

                <div class="mt-6 flex items-center justify-end gap-2">
                    <a href="{{ route('teacher.assignments.index') }}"
                       class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                        Simpan draf
                    </button>
                </div>
            </form>
        </x-ui.card>
    @endif
</x-layouts.portal>
