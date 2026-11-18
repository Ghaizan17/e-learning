@php
    use App\Enums\CourseStatus;
    use App\Enums\LessonType;
    use Illuminate\Support\Facades\Storage;

    $statusColor = match ($course->status) {
        CourseStatus::Published => 'success',
        CourseStatus::Archived => 'warning',
        default => 'slate',
    };

    $typeColor = fn (LessonType $type): string => match ($type) {
        LessonType::Video => 'danger',
        LessonType::Pdf => 'warning',
        LessonType::Document, LessonType::Link => 'indigo',
        LessonType::Code => 'success',
        default => 'slate',
    };
@endphp

<x-layouts.portal :title="$course->title">
    <x-ui.page-header :title="$course->title" description="{{ $course->teacherAssignment?->classroom?->name }} &middot; {{ $course->teacherAssignment?->subject?->name }} &middot; {{ $course->teacherAssignment?->semester?->name?->label() }}">
        <x-slot:actions>
            <x-ui.badge :color="$statusColor">{{ $course->status->label() }}</x-ui.badge>
            <a href="{{ route('teacher.courses.students', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="users" class="h-4 w-4" />
                Siswa
            </a>
            <a href="{{ route('teacher.courses.edit', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="pencil" class="h-4 w-4" />
                Edit
            </a>
            <a href="{{ route('teacher.courses.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Daftar
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Bab" :value="$chapters->count()" />
        <x-ui.stat label="Materi terbit" :value="$course->publishedLessons()->count()" />
        <x-ui.stat label="Siswa terdaftar" :value="$enrollmentCount" :hint="'dari '.$studentCount.' siswa di kelas'" />
        <x-ui.stat label="Total materi" :value="$chapters->sum(fn ($chapter) => $chapter->lessons->count())" />
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        @if ($course->status !== CourseStatus::Published)
            <form method="POST" action="{{ route('teacher.courses.publish', $course) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <x-ui.icon name="check" class="h-4 w-4" />
                    Terbitkan kursus
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('teacher.courses.archive', $course) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-3.5 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                    Arsipkan kursus
                </button>
            </form>
        @endif
        <p class="text-xs text-slate-500">
            Kursus hanya bisa diterbitkan jika minimal satu bab dan satu materi sudah diterbitkan.
        </p>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($chapters as $chapter)
            <x-ui.card padded="false">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                {{ $chapter->position }}
                            </span>
                            <h2 class="text-sm font-semibold text-slate-900">{{ $chapter->title }}</h2>
                            <x-ui.badge :color="$chapter->is_published ? 'success' : 'slate'">
                                {{ $chapter->is_published ? 'Terbit' : 'Draf' }}
                            </x-ui.badge>
                        </div>
                        @if ($chapter->description)
                            <p class="mt-1 text-sm text-slate-500">{{ $chapter->description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST"
                              action="{{ route($chapter->is_published ? 'teacher.chapters.unpublish' : 'teacher.chapters.publish', $chapter) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                {{ $chapter->is_published ? 'Sembunyikan' : 'Terbitkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('teacher.chapters.destroy', $chapter) }}"
                              onsubmit="return confirm('Hapus bab ini beserta materinya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                Hapus
                            </button>
                        </form>
                    </div>
                </header>

                <div class="divide-y divide-slate-100">
                    @forelse ($chapter->lessons as $lesson)
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-semibold text-slate-400">{{ $chapter->position }}.{{ $lesson->position }}</span>
                                        <p class="text-sm font-medium text-slate-900">{{ $lesson->title }}</p>
                                        <x-ui.badge :color="$typeColor($lesson->type)">{{ $lesson->type->label() }}</x-ui.badge>
                                        <x-ui.badge :color="$lesson->is_published ? 'success' : 'slate'">
                                            {{ $lesson->is_published ? 'Terbit' : 'Draf' }}
                                        </x-ui.badge>
                                        @if ($lesson->estimated_duration_minutes)
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                                                <x-ui.icon name="clock" class="h-3.5 w-3.5" />
                                                {{ $lesson->estimated_duration_minutes }} menit
                                            </span>
                                        @endif
                                    </div>
                                    @if ($lesson->video_url)
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $lesson->video_url }}</p>
                                    @endif
                                    @if ($lesson->external_url)
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $lesson->external_url }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <form method="POST"
                                          action="{{ route($lesson->is_published ? 'teacher.lessons.unpublish' : 'teacher.lessons.publish', $lesson) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            {{ $lesson->is_published ? 'Sembunyikan' : 'Terbitkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.lessons.destroy', $lesson) }}"
                                          onsubmit="return confirm('Hapus materi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                                @foreach ($lesson->attachments as $attachment)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-slate-600">
                                        <x-ui.icon name="paper-clip" class="h-3.5 w-3.5" />
                                        {{ $attachment->title }} ({{ $attachment->humanSize() }})
                                        <form method="POST" action="{{ route('teacher.attachments.destroy', $attachment) }}"
                                              onsubmit="return confirm('Hapus lampiran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-700" aria-label="Hapus lampiran">
                                                <x-ui.icon name="x" class="h-3.5 w-3.5" />
                                            </button>
                                        </form>
                                    </span>
                                @endforeach

                                <details class="inline-block">
                                    <summary class="cursor-pointer text-indigo-600 hover:text-indigo-700">Tambah lampiran</summary>
                                    <form method="POST" action="{{ route('teacher.lessons.attachments.store', $lesson) }}"
                                          enctype="multipart/form-data" class="mt-2 flex flex-wrap items-end gap-2">
                                        @csrf
                                        <input type="text" name="title" placeholder="Judul lampiran"
                                               class="w-40 rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                                        <input type="file" name="file" required
                                               class="text-xs text-slate-600 file:mr-2 file:rounded-md file:border-0 file:bg-indigo-50 file:px-2 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700">
                                        <button type="submit"
                                                class="rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-slate-900">
                                            Unggah
                                        </button>
                                    </form>
                                </details>
                            </div>

                            <details class="mt-3">
                                <summary class="cursor-pointer text-xs font-medium text-slate-500 hover:text-slate-700">Edit materi</summary>
                                <form method="POST" action="{{ route('teacher.lessons.update', $lesson) }}" class="mt-3 grid gap-3 md:grid-cols-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="md:col-span-2">
                                        <x-ui.input name="title" label="Judul" required :value="$lesson->title" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Tipe</label>
                                        <select name="type" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                            @foreach (LessonType::cases() as $type)
                                                <option value="{{ $type->value }}" @selected($lesson->type === $type)>{{ $type->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-ui.input name="estimated_duration_minutes" type="number" label="Estimasi (menit)"
                                                    :value="$lesson->estimated_duration_minutes" min="1" max="600" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700">Konten HTML</label>
                                        <textarea name="content_html" rows="4"
                                                  class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs">{{ $lesson->content_html }}</textarea>
                                    </div>
                                    <div>
                                        <x-ui.input name="video_url" type="url" label="URL video" :value="$lesson->video_url" />
                                    </div>
                                    <div>
                                        <x-ui.input name="external_url" type="url" label="URL eksternal" :value="$lesson->external_url" />
                                    </div>
                                    <div>
                                        <x-ui.input name="code_language" label="Bahasa kode" :value="$lesson->code_language" placeholder="php, javascript, ..." />
                                    </div>
                                    <div class="flex items-end">
                                        <button type="submit"
                                                class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Simpan materi
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-slate-500">Belum ada materi pada bab ini.</p>
                    @endforelse
                </div>

                <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-3">
                    <details>
                        <summary class="cursor-pointer text-sm font-medium text-indigo-600 hover:text-indigo-700">Tambah materi</summary>
                        <form method="POST" action="{{ route('teacher.lessons.store', $chapter) }}" class="mt-3 grid gap-3 md:grid-cols-2">
                            @csrf
                            <div class="md:col-span-2">
                                <x-ui.input name="title" label="Judul materi" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tipe</label>
                                <select name="type" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                    @foreach (LessonType::cases() as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-ui.input name="estimated_duration_minutes" type="number" label="Estimasi (menit)" min="1" max="600" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700">Konten HTML</label>
                                <textarea name="content_html" rows="4"
                                          class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"
                                          placeholder="<p>Isi materi…</p>"></textarea>
                            </div>
                            <div>
                                <x-ui.input name="video_url" type="url" label="URL video" />
                            </div>
                            <div>
                                <x-ui.input name="external_url" type="url" label="URL eksternal" />
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit"
                                        class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Simpan materi
                                </button>
                            </div>
                        </form>
                    </details>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Belum ada bab" description="Tambahkan bab pertama untuk mulai menyusun materi." />
        @endforelse
    </div>

    <div class="mt-6">
        <x-ui.card title="Tambah bab baru">
            <form method="POST" action="{{ route('teacher.chapters.store', $course) }}" class="grid gap-3 md:grid-cols-3">
                @csrf
                <div class="md:col-span-1">
                    <x-ui.input name="title" label="Judul bab" required placeholder="Bab 1: Pengenalan" />
                </div>
                <div class="md:col-span-1">
                    <x-ui.input name="description" label="Deskripsi singkat" />
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3.5 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                        <x-ui.icon name="plus" class="h-4 w-4" />
                        Tambah bab
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.portal>
