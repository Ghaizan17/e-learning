@php
    use App\Enums\CourseStatus;
    use Illuminate\Support\Facades\Storage;
@endphp

<x-layouts.portal title="Kursus">
    <x-ui.page-header
        title="Kursus Saya"
        description="Kelola bab, materi, lampiran, dan publikasi kursus.">
        <x-slot:actions>
            <a href="{{ route('teacher.courses.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <x-ui.icon name="plus" class="h-4 w-4" />
                Buat kursus
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($courses->isEmpty())
        <x-ui.empty-state
            title="Belum ada kursus"
            description="Buat kursus pertama Anda dari penugasan mengajar yang tersedia.">
            <x-slot:action>
                <a href="{{ route('teacher.courses.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Buat kursus
                </a>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($courses as $course)
                @php($color = match ($course->status) {
                    CourseStatus::Published => 'success',
                    CourseStatus::Archived => 'warning',
                    default => 'slate',
                })
                <x-ui.card padded="false" class="flex h-full flex-col overflow-hidden">
                    <div class="aspect-[16/9] w-full bg-slate-100">
                        @if ($course->thumbnail_path)
                            <img src="{{ Storage::disk('public')->url($course->thumbnail_path) }}"
                                 alt="Sampul {{ $course->title }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-slate-300">
                                <x-ui.icon name="book" class="h-10 w-10" />
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col px-5 py-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold leading-snug text-slate-900">{{ $course->title }}</p>
                            <x-ui.badge :color="$color">{{ $course->status->label() }}</x-ui.badge>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $course->teacherAssignment?->classroom?->name }} &middot;
                            {{ $course->teacherAssignment?->subject?->name }} &middot;
                            {{ $course->teacherAssignment?->semester?->name?->label() }}
                        </p>
                        <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <x-ui.icon name="book" class="h-4 w-4" />
                                {{ $course->chapters_count }} bab
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <x-ui.icon name="users" class="h-4 w-4" />
                                {{ $course->enrollments_count }} siswa
                            </span>
                        </div>
                    </div>
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('teacher.courses.show', $course) }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Kelola kursus</a>
                        <a href="{{ route('teacher.courses.students', $course) }}"
                           class="text-xs font-medium text-slate-500 hover:text-slate-700">Siswa</a>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $courses->links() }}
        </div>
    @endif
</x-layouts.portal>
