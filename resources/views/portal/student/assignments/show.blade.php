@php
    use App\Enums\SubmissionStatus;
@endphp

<x-layouts.portal :title="$assignment->title">
    <x-ui.page-header :title="$assignment->title"
                      :description="$assignment->course?->title.' · '.$assignment->course?->teacherAssignment?->subject?->name">
        <x-slot:actions>
            <a href="{{ route('student.assignments.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Daftar tugas
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if (session('status'))
        <x-ui.alert class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Tenggat" :value="$assignment->due_at->translatedFormat('d M Y')" :hint="$assignment->due_at->translatedFormat('H:i').' WIB'" />
        <x-ui.stat label="Skor maksimal" :value="$assignment->max_score" />
        <x-ui.stat label="Status"
                   :value="$submission?->status->label() ?? 'Belum dikumpulkan'"
                   :hint="$submission?->submitted_at ? 'Dikumpulkan '.$submission->submitted_at->translatedFormat('d M Y H:i') : null" />
        <x-ui.stat label="Nilai"
                   :value="$submission?->grade?->score ? $submission->grade->score.' / '.$assignment->max_score : '—'"
                   :hint="$submission?->grade ? 'Dinilai '.$submission->grade->graded_at->translatedFormat('d M Y') : 'Menunggu penilaian'" />
    </div>

    @if ($submission?->grade?->feedback)
        <x-ui.card class="mt-6" title="Umpan balik guru">
            <p class="whitespace-pre-line text-sm text-slate-700">{{ $submission->grade->feedback }}</p>
        </x-ui.card>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Instruksi">
            <div class="whitespace-pre-line text-sm text-slate-700">{{ $assignment->instructions }}</div>
        </x-ui.card>

        <x-ui.card title="Lampiran">
            <ul class="space-y-2">
                @forelse ($assignment->attachments as $attachment)
                    <li class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $attachment->title }}</p>
                            <span class="text-xs text-slate-500">{{ $attachment->humanSize() }}</span>
                        </div>
                        <a href="{{ route('assignment-attachments.download', $attachment) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                            <x-ui.icon name="download" class="h-3.5 w-3.5" />
                            Unduh
                        </a>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Tidak ada lampiran.</li>
                @endforelse
            </ul>
        </x-ui.card>
    </div>

    @if ($submission && $submission->files->isNotEmpty())
        <x-ui.card class="mt-6" title="Berkas yang Anda kumpulkan">
            <ul class="space-y-2">
                @foreach ($submission->files as $file)
                    <li class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2">
                        <span class="truncate text-sm text-slate-700">{{ $file->original_name }}</span>
                        <a href="{{ route('submission-files.download', $file) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                            <x-ui.icon name="download" class="h-3.5 w-3.5" />
                            Unduh
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    <x-ui.card class="mt-6" title="{{ $submission ? 'Kumpulkan ulang' : 'Kumpulkan tugas' }}">
        @if ($canSubmit)
            @if ($submission)
                <x-ui.alert type="warning" class="mb-4">
                    Anda sudah mengumpulkan (percobaan #{{ $submission->attempt_no }}). Pengumpulan baru akan menjadi percobaan #{{ $submission->attempt_no + 1 }}.
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="content" class="block text-sm font-medium text-slate-700">Jawaban / catatan</label>
                    <textarea id="content" name="content" rows="6" maxlength="20000"
                              placeholder="Tulis jawaban Anda, atau lampirkan berkas di bawah."
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="files" class="block text-sm font-medium text-slate-700">Berkas (opsional)</label>
                    <input id="files" name="files[]" type="file" multiple
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.png,.jpg,.jpeg,.txt"
                           class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-slate-500">Maksimal 5 berkas, 10 MB per berkas.</p>
                    @error('files')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('files.*')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                @if ($assignment->due_at->isPast())
                    <x-ui.alert type="warning">
                        Tenggat sudah lewat. Pengumpulan masih diterima namun ditandai terlambat.
                    </x-ui.alert>
                @endif

                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <x-ui.icon name="check" class="h-4 w-4" />
                    Kumpulkan
                </button>
            </form>
        @elseif ($submission)
            <p class="text-sm text-slate-600">
                Tugas ini tidak mengizinkan pengumpulan ulang. Hubungi guru jika Anda perlu memperbaiki jawaban.
            </p>
        @else
            <p class="text-sm text-slate-600">Anda belum terdaftar pada kursus ini.</p>
        @endif
    </x-ui.card>
</x-layouts.portal>
