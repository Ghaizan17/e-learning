@php
    use App\Enums\LessonProgressStatus;
    use App\Enums\LessonType;

    $continue = null;

    foreach ($chapters as $chapter) {
        foreach ($chapter->lessons as $lesson) {
            $record = $progress[$lesson->getKey()] ?? null;

            if ($continue === null && $record?->status !== LessonProgressStatus::Completed) {
                $continue = $lesson;
            }
        }
    }
@endphp

<x-layouts.portal :title="$course->title">
    <x-ui.page-header :title="$course->title" description="{{ $course->teacherAssignment?->subject?->name }} &middot; {{ $course->teacherAssignment?->teacher?->name }}">
        <x-slot:actions>
            <a href="{{ route('student.courses.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kursus saya
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            @forelse ($chapters as $chapter)
                <x-ui.card :title="$chapter->title" :description="$chapter->description">
                    @if ($chapter->lessons->isEmpty())
                        <p class="text-sm text-slate-500">Belum ada materi pada bab ini.</p>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($chapter->lessons as $lesson)
                                @php
                                    $record = $progress[$lesson->getKey()] ?? null;
                                    $done = $record?->status === LessonProgressStatus::Completed;
                                @endphp
                                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span @class([
                                            'flex h-7 w-7 shrink-0 items-center justify-center rounded-full',
                                            'bg-emerald-100 text-emerald-700' => $done,
                                            'bg-slate-100 text-slate-400' => ! $done,
                                        ])>
                                            <x-ui.icon :name="$done ? 'check' : 'play'" class="h-3.5 w-3.5" />
                                        </span>
                                        <div class="min-w-0">
                                            <a href="{{ route('student.lessons.show', [$course, $lesson]) }}"
                                               class="block truncate text-sm font-medium text-slate-900 hover:text-indigo-600">
                                                {{ $lesson->title }}
                                            </a>
                                            <p class="text-xs text-slate-500">
                                                {{ $lesson->type->label() }}
                                                @if ($lesson->estimated_duration_minutes)
                                                    &middot; {{ $lesson->estimated_duration_minutes }} menit
                                                @endif
                                                @if ($record?->status === LessonProgressStatus::InProgress)
                                                    &middot; <span class="text-amber-600">sedang dipelajari</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('student.lessons.show', [$course, $lesson]) }}"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                        {{ $done ? 'Ulas' : 'Pelajari' }}
                                        <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
            @empty
                <x-ui.empty-state title="Materi belum tersedia"
                                  description="Guru belum menerbitkan bab untuk kursus ini." />
            @endforelse
        </div>

        <div class="space-y-4">
            <x-ui.card title="Progres belajar">
                <x-ui.progress :value="$percent" />
                <p class="mt-2 text-sm text-slate-600">
                    {{ $completed }} dari {{ $totalLessons }} materi selesai
                </p>
                @if ($continue)
                    <a href="{{ route('student.lessons.show', [$course, $continue]) }}"
                       class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Lanjutkan belajar
                        <x-ui.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @endif
            </x-ui.card>

            @if ($course->description)
                <x-ui.card title="Tentang kursus">
                    <p class="text-sm leading-relaxed text-slate-600">{{ $course->description }}</p>
                </x-ui.card>
            @endif

            <x-ui.card title="Kelas">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Kelas</dt>
                        <dd class="font-medium text-slate-900">{{ $course->teacherAssignment?->classroom?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Guru</dt>
                        <dd class="font-medium text-slate-900">{{ $course->teacherAssignment?->teacher?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Semester</dt>
                        <dd class="font-medium text-slate-900">{{ $course->teacherAssignment?->semester?->name?->label() ?? '-' }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>
    </div>
</x-layouts.portal>
