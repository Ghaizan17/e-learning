@php
    $quiz = $quiz ?? null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    @unless ($quiz)
        <div class="sm:col-span-2">
            <label for="course_id" class="block text-sm font-medium text-slate-700">Kursus</label>
            <select id="course_id" name="course_id" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Pilih kursus</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->getKey() }}" @selected(old('course_id') == $course->getKey())>
                        {{ $course->title }} — {{ $course->teacherAssignment?->classroom?->name }}
                    </option>
                @endforeach
            </select>
            @error('course_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    @endunless

    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Judul</label>
        <input id="title" name="title" type="text" required maxlength="150"
               value="{{ old('title', $quiz?->title) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-slate-700">Jenis</label>
        <select id="type" name="type" required
                class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm">
            @foreach (['quiz' => 'Kuis', 'exam' => 'Ujian'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $quiz?->type?->value ?? 'quiz') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
        <textarea id="description" name="description" rows="3" maxlength="2000"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('description', $quiz?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="duration_minutes" class="block text-sm font-medium text-slate-700">Durasi (menit)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="1" max="300" required
               value="{{ old('duration_minutes', $quiz?->duration_minutes ?? 60) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        @error('duration_minutes')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="max_attempts" class="block text-sm font-medium text-slate-700">Maksimal percobaan</label>
        <input id="max_attempts" name="max_attempts" type="number" min="1" max="10" required
               value="{{ old('max_attempts', $quiz?->max_attempts ?? 1) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        @error('max_attempts')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="starts_at" class="block text-sm font-medium text-slate-700">Mulai (opsional)</label>
        <input id="starts_at" name="starts_at" type="datetime-local"
               value="{{ old('starts_at', $quiz?->starts_at?->format('Y-m-d\TH:i')) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        @error('starts_at')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="ends_at" class="block text-sm font-medium text-slate-700">Selesai (opsional)</label>
        <input id="ends_at" name="ends_at" type="datetime-local"
               value="{{ old('ends_at', $quiz?->ends_at?->format('Y-m-d\TH:i')) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        @error('ends_at')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="passing_score" class="block text-sm font-medium text-slate-700">Nilai kelulusan</label>
        <input id="passing_score" name="passing_score" type="number" min="0" max="100" step="0.01" required
               value="{{ old('passing_score', $quiz?->passing_score ?? 70) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm">
        @error('passing_score')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-2 sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $quiz?->shuffle_questions))
                   class="h-4 w-4 rounded border-slate-300 text-indigo-600">
            Acak urutan soal
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options', $quiz?->shuffle_options))
                   class="h-4 w-4 rounded border-slate-300 text-indigo-600">
            Acak urutan pilihan jawaban
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="show_result_immediately" value="1" @checked(old('show_result_immediately', $quiz?->show_result_immediately ?? true))
                   class="h-4 w-4 rounded border-slate-300 text-indigo-600">
            Tampilkan hasil langsung setelah mengumpulkan
        </label>
    </div>
</div>
