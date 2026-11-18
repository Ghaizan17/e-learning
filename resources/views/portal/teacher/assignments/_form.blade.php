@php
    $assignment = $assignment ?? null;
@endphp

<div class="grid gap-4">
    @unless ($assignment)
        <div>
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
            @error('course_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    @endunless

    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Judul tugas</label>
        <input id="title" name="title" type="text" required maxlength="150"
               value="{{ old('title', $assignment?->title) }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        @error('title')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="instructions" class="block text-sm font-medium text-slate-700">Instruksi</label>
        <textarea id="instructions" name="instructions" rows="8" required maxlength="20000"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('instructions', $assignment?->instructions) }}</textarea>
        @error('instructions')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="due_at" class="block text-sm font-medium text-slate-700">Tenggat</label>
            <input id="due_at" name="due_at" type="datetime-local" required
                   value="{{ old('due_at', $assignment?->due_at?->format('Y-m-d\TH:i')) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @error('due_at')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="max_score" class="block text-sm font-medium text-slate-700">Skor maksimal</label>
            <input id="max_score" name="max_score" type="number" min="1" max="100" step="0.01"
                   value="{{ old('max_score', $assignment?->max_score ?? 100) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            @error('max_score')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="allow_resubmission" value="1" @checked(old('allow_resubmission', $assignment?->allow_resubmission))
               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        Izinkan siswa mengumpulkan ulang (percobaan baru)
    </label>

    @unless ($assignment)
        <div>
            <label for="attachments" class="block text-sm font-medium text-slate-700">Lampiran (opsional)</label>
            <input id="attachments" name="attachments[]" type="file" multiple
                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.png,.jpg,.jpeg,.txt"
                   class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
            <p class="mt-1 text-xs text-slate-500">Maksimal 5 berkas, 10 MB per berkas.</p>
            @error('attachments')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            @error('attachments.*')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    @endunless
</div>
