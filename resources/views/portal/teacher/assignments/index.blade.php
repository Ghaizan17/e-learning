@php
    use App\Enums\SubmissionStatus;
@endphp

<x-layouts.portal title="Tugas">
    <x-ui.page-header title="Tugas" description="Kelola tugas, tenggat, dan pengumpulan siswa.">
        <x-slot:actions>
            <a href="{{ route('teacher.assignments.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Buat tugas
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card padded="false">
        <div class="divide-y divide-slate-100">
            @forelse ($assignments as $assignment)
                <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('teacher.assignments.show', $assignment) }}"
                               class="text-sm font-semibold text-slate-900 hover:text-indigo-600">{{ $assignment->title }}</a>
                            <x-ui.badge :color="$assignment->is_published ? 'success' : 'slate'">
                                {{ $assignment->is_published ? 'Terbit' : 'Draf' }}
                            </x-ui.badge>
                            @if ($assignment->due_at->isPast())
                                <x-ui.badge color="danger">Lewat tenggat</x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $assignment->course?->title }}
                            &middot; {{ $assignment->course?->teacherAssignment?->classroom?->name }}
                            &middot; tenggat {{ $assignment->due_at->translatedFormat('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span>{{ $assignment->submissions_count }} pengumpulan</span>
                        <a href="{{ route('teacher.assignments.show', $assignment) }}"
                           class="font-semibold text-indigo-600 hover:text-indigo-700">Kelola</a>
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada tugas"
                                  description="Buat tugas pertama untuk kursus yang Anda ampu." />
            @endforelse
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $assignments->links() }}</div>
</x-layouts.portal>
