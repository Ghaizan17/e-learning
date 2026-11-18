@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-layouts.portal title="Edit Kursus">
    <x-ui.page-header
        title="Edit Kursus"
        description="{{ $course->teacherAssignment?->classroom?->name }} &middot; {{ $course->teacherAssignment?->subject?->name }}">
        <x-slot:actions>
            <a href="{{ route('teacher.courses.show', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('teacher.courses.update', $course) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PATCH')

        <x-ui.card title="Detail kursus">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-ui.input name="title" label="Judul kursus" required :value="old('title', $course->title)" />
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4"
                              class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">{{ old('description', $course->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    @if ($course->thumbnail_path)
                        <img src="{{ Storage::disk('public')->url($course->thumbnail_path) }}"
                             alt="Sampul {{ $course->title }}"
                             class="mb-3 h-32 w-52 rounded-lg border border-slate-200 object-cover">
                        <label class="mb-3 flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remove_thumbnail" value="1"
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Hapus sampul saat ini
                        </label>
                    @endif
                    <label for="thumbnail" class="block text-sm font-medium text-slate-700">Ganti sampul</label>
                    <input id="thumbnail" name="thumbnail" type="file" accept="image/*"
                           class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700">
                    <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    @error('thumbnail')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Zona berbahaya" description="Menghapus kursus juga menghapus bab dan materi di dalamnya.">
            <button type="button"
                    data-confirm-form="delete-course-form"
                    data-confirm="Hapus kursus ini? Tindakan ini tidak dapat dibatalkan."
                    class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                <x-ui.icon name="trash" class="h-4 w-4" />
                Hapus kursus
            </button>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('teacher.courses.show', $course) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                Simpan perubahan
            </button>
        </div>
    </form>

    <form id="delete-course-form" method="POST" action="{{ route('teacher.courses.destroy', $course) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.portal>
