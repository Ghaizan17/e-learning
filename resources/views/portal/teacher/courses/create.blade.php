<x-layouts.portal title="Buat Kursus">
    <x-ui.page-header
        title="Buat Kursus"
        description="Kursus baru selalu dimulai sebagai draf dan hanya terlihat oleh Anda.">
        <x-slot:actions>
            <a href="{{ route('teacher.courses.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($assignments->isEmpty())
        <x-ui.alert type="warning">
            Anda belum memiliki penugasan mengajar. Minta admin menetapkan kelas dan mata pelajaran terlebih dahulu.
        </x-ui.alert>
    @else
        <form method="POST" action="{{ route('teacher.courses.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <x-ui.card title="Detail kursus">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="teacher_assignment_id" class="block text-sm font-medium text-slate-700">
                            Penugasan mengajar <span class="text-rose-600">*</span>
                        </label>
                        <select id="teacher_assignment_id" name="teacher_assignment_id" required
                                class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                            <option value="">Pilih kelas &amp; mata pelajaran</option>
                            @foreach ($assignments as $assignment)
                                <option value="{{ $assignment->getKey() }}"
                                    @selected((int) old('teacher_assignment_id', $selectedAssignment) === $assignment->getKey())>
                                    {{ $assignment->classroom?->name }} &middot; {{ $assignment->subject?->name }}
                                    &middot; {{ $assignment->semester?->name?->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_assignment_id')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.input name="title" label="Judul kursus" required :value="old('title')"
                                    placeholder="Contoh: Pemrograman Web Dasar" />
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
                        <textarea id="description" name="description" rows="4"
                                  class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                  placeholder="Ringkasan singkat isi kursus">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="thumbnail" class="block text-sm font-medium text-slate-700">Sampul kursus</label>
                        <input id="thumbnail" name="thumbnail" type="file" accept="image/*"
                               class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700">
                        <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('teacher.courses.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    Simpan kursus
                </button>
            </div>
        </form>
    @endif
</x-layouts.portal>
