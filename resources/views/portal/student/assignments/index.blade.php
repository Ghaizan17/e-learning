@php
    use App\Enums\SubmissionStatus;use Illuminate\Support\Str;
@endphp

<x-layouts.portal title="Tugas">
    <x-ui.page-header title="Tugas" description="Semua tugas dari kursus yang Anda ikuti." />

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card padded="false">
        <div class="divide-y divide-slate-100">
            @forelse ($assignments as $assignment)
                @php($submission = $assignment->submissions->first())
                <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('student.assignments.show', $assignment) }}"
                               class="text-sm font-semibold text-slate-900 hover:text-indigo-600">{{ $assignment->title }}</a>
                            @if ($submission)
                                <x-ui.badge :color="$submission->status->color()">{{ $submission->status->label() }}</x-ui.badge>
                                @if ($submission->status === SubmissionStatus::Graded && $submission->grade)
                                    <x-ui.badge color="success">{{ $submission->grade->score }}/{{ $assignment->max_score }}</x-ui.badge>
                                @endif
                            @else
                                <x-ui.badge color="slate">Belum dikumpulkan</x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $assignment->course?->title }} &middot; {{ $assignment->course?->teacherAssignment?->subject?->name }}
                        </p>
                    </div>
                    <div class="text-right text-xs">
                        <p class="font-medium {{ $assignment->due_at->isPast() ? 'text-rose-600' : 'text-slate-600' }}">
                            Tenggat {{ $assignment->due_at->translatedFormat('d M Y H:i') }}
                        </p>
                        @if ($submission?->grade)
                            <p class="mt-1 text-slate-500">Dinilai {{ $submission->grade->graded_at->translatedFormat('d M Y') }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada tugas"
                                  description="Tugas dari kursus Anda akan muncul di sini." />
            @endforelse
        </div>
    </x-ui.card>

    <div class="mt-4">{{ $assignments->links() }}</div>
</x-layouts.portal>
