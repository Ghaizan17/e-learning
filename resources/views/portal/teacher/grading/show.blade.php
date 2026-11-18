<x-layouts.portal :title="'Buku Nilai · '.$course->title">
    <x-ui.page-header :title="$course->title" description="Buku nilai kelas">
        <x-slot:actions>
            <a href="{{ route('teacher.grading.show', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Skema nilai
            </a>
            <a href="{{ route('teacher.grades.export', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                <x-ui.icon name="download" class="h-4 w-4" />
                Ekspor CSV
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if ($components->isEmpty())
        <x-ui.empty-state title="Belum ada komponen nilai"
            description="Komponen dibuat otomatis saat tugas atau kuis diterbitkan." />
    @elseif ($enrollments->isEmpty())
        <x-ui.empty-state title="Belum ada siswa" description="Siswa akan muncul setelah sinkronisasi enrollment." />
    @else
        <x-ui.card title="Matriks nilai" description="Ubah skor, isi alasan perubahan, lalu simpan. Setiap perubahan tercatat pada riwayat.">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="sticky left-0 z-10 bg-white px-3 py-2">Siswa</th>
                            @foreach ($components as $component)
                                <th class="min-w-40 px-3 py-2 align-top">
                                    <span class="block font-semibold text-slate-700">{{ $component->title }}</span>
                                    <span class="text-[11px] font-normal text-slate-400">
                                        {{ $component->category?->name?->label() }} · maks {{ $component->max_score }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="px-3 py-2 align-top">Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($enrollments as $enrollment)
                            <tr class="align-top">
                                <td class="sticky left-0 z-10 bg-white px-3 py-3">
                                    <p class="font-medium text-slate-800">{{ $enrollment->user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $enrollment->user?->email }}</p>
                                </td>

                                @foreach ($components as $component)
                                    @php($grade = $grades->get($enrollment->user_id.'-'.$component->getKey()))
                                    <td class="px-3 py-3">
                                        @if ($grade)
                                            <form method="POST" action="{{ route('teacher.grades.update', $grade) }}" class="space-y-1">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="score" step="0.01" min="0" max="{{ $grade->max_score }}"
                                                       value="{{ $grade->score }}" aria-label="Skor {{ $component->title }}"
                                                       class="w-24 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                                <input type="text" name="reason" maxlength="500" placeholder="Alasan (opsional)"
                                                       aria-label="Alasan perubahan"
                                                       class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                                <label class="flex items-center gap-1.5 text-xs text-slate-600">
                                                    <input type="hidden" name="is_published" value="0">
                                                    <input type="checkbox" name="is_published" value="1" @checked($grade->is_published)
                                                           class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600">
                                                    Tampil ke siswa
                                                </label>
                                                <button type="submit"
                                                        class="rounded-md bg-slate-800 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-900">
                                                    Simpan
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-3 py-3">
                                    <p class="font-semibold text-slate-800">{{ $enrollment->final_score ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $enrollment->letter_grade ?? '—' }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif
</x-layouts.portal>
