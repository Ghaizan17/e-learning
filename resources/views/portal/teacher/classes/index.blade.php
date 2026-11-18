@php
    use App\Enums\CourseStatus;
    use App\Enums\GradeLevel;
@endphp

<x-layouts.portal title="Kelas Saya">
    <x-ui.page-header
        title="Kelas Saya"
        description="Penugasan mengajar dan kursus yang Anda ampu.">
        <x-slot:actions>
            <a href="{{ route('teacher.courses.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Buat kursus
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($assignments->isEmpty())
        <x-ui.empty-state
            title="Belum ada penugasan mengajar"
            description="Admin perlu menetapkan kelas dan mata pelajaran untuk Anda sebelum kursus dapat dibuat." />
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($assignments as $assignment)
                <x-ui.card padded="false" class="flex h-full flex-col">
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $assignment->classroom?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $assignment->subject?->name ?? '-' }}</p>
                            </div>
                            <x-ui.badge color="indigo">{{ $assignment->classroom?->grade_level?->label() ?? '-' }}</x-ui.badge>
                        </div>
                        <dl class="mt-4 space-y-2 text-xs text-slate-500">
                            <div class="flex items-center justify-between">
                                <dt>Semester</dt>
                                <dd class="font-medium text-slate-700">{{ $assignment->semester?->name?->label() ?? '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Tahun ajaran</dt>
                                <dd class="font-medium text-slate-700">{{ $assignment->academicYear?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Kursus</dt>
                                <dd class="font-medium text-slate-700">{{ $assignment->courses_count }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="mt-auto flex items-center gap-2 border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('teacher.courses.index') }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Lihat kursus</a>
                        <a href="{{ route('teacher.courses.create', ['teacher_assignment_id' => $assignment->getKey()]) }}"
                           class="ml-auto inline-flex items-center gap-1 text-sm font-medium text-slate-600 hover:text-slate-900">
                            Buat kursus
                            <x-ui.icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
