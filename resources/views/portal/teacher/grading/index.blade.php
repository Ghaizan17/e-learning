<x-layouts.portal title="Buku Nilai">
    <x-ui.page-header title="Buku Nilai" description="Pilih kursus untuk membuka matriks nilai dan skema penilaian." />

    @if ($courses->isEmpty())
        <x-ui.empty-state title="Belum ada kursus" description="Kursus yang Anda ampu akan muncul di sini." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($courses as $course)
                <x-ui.card :title="$course->title" :description="$course->teacherAssignment?->classroom?->name">
                    <p class="text-xs text-slate-500">{{ $course->teacherAssignment?->subject?->name }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('teacher.grades.show', $course) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            <x-ui.icon name="chart" class="h-4 w-4" />
                            Buku nilai
                        </a>
                        <a href="{{ route('teacher.grading.show', $course) }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Skema
                        </a>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
