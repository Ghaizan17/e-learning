<x-layouts.portal title="Nilai Saya">
    <x-ui.page-header title="Nilai Saya" description="Nilai yang sudah dipublikasikan oleh guru." />

    @if ($enrollments->isEmpty())
        <x-ui.empty-state title="Belum ada nilai" description="Nilai akan muncul setelah Anda terdaftar dan dinilai pada suatu kursus." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($enrollments as $enrollment)
                <x-ui.card :title="$enrollment->course?->title" :description="$enrollment->course?->teacherAssignment?->classroom?->name">
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Nilai akhir</p>
                            <p class="text-2xl font-semibold text-slate-900">{{ $enrollment->final_score ?? '—' }}</p>
                        </div>
                        <x-ui.badge color="info">{{ $enrollment->letter_grade ?? '—' }}</x-ui.badge>
                    </div>
                    <a href="{{ route('student.grades.show', $enrollment->course) }}"
                       class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                        Lihat rincian
                        <x-ui.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
