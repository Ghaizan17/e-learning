@php
    use App\Enums\SubmissionStatus;

    $gradedCount = $submissions->filter(fn ($submission) => $submission->grade !== null)->count();
@endphp

<x-layouts.portal :title="$assignment->title">
    <x-ui.page-header :title="$assignment->title"
                      :description="$assignment->course?->title.' · '.$assignment->course?->teacherAssignment?->classroom?->name">
        <x-slot:actions>
            <x-ui.badge :color="$assignment->is_published ? 'success' : 'slate'">
                {{ $assignment->is_published ? 'Terbit' : 'Draf' }}
            </x-ui.badge>
            <a href="{{ route('teacher.assignments.edit', $assignment) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="pencil" class="h-4 w-4" />
                Edit
            </a>
            <a href="{{ route('teacher.assignments.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Daftar
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
        <x-ui.stat label="Pengumpulan" :value="$submissions->count()" :hint="'dari '.$enrolledCount.' siswa terdaftar'" />
        <x-ui.stat label="Sudah dinilai" :value="$gradedCount" />
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        <form method="POST" action="{{ route($assignment->is_published ? 'teacher.assignments.unpublish' : 'teacher.assignments.publish', $assignment) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold shadow-sm {{ $assignment->is_published ? 'border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                <x-ui.icon name="check" class="h-4 w-4" />
                {{ $assignment->is_published ? 'Sembunyikan dari siswa' : 'Terbitkan tugas' }}
            </button>
        </form>
        <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}"
              onsubmit="return confirm('Hapus tugas ini beserta seluruh pengumpulannya?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                <x-ui.icon name="trash" class="h-4 w-4" />
                Hapus tugas
            </button>
        </form>
        @unless ($assignment->is_published)
            <p class="text-xs text-slate-500">Siswa hanya melihat tugas setelah diterbitkan.</p>
        @endunless
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Instruksi">
            <div class="prose prose-slate max-w-none whitespace-pre-line text-sm text-slate-700">{{ $assignment->instructions }}</div>
        </x-ui.card>

        <x-ui.card title="Lampiran tugas">
            <ul class="space-y-2">
                @forelse ($assignment->attachments as $attachment)
                    <li class="flex items-start justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2">
                        <div class="min-w-0">
                            <a href="{{ route('assignment-attachments.download', $attachment) }}"
                               class="block truncate text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ $attachment->title }}</a>
                            <span class="text-xs text-slate-500">{{ $attachment->humanSize() }}</span>
                        </div>
                        <form method="POST" action="{{ route('teacher.assignment-attachments.destroy', $attachment) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-700" title="Hapus lampiran">
                                <x-ui.icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Belum ada lampiran.</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('teacher.assignments.attachments.store', $assignment) }}" enctype="multipart/form-data" class="mt-4 space-y-2">
                @csrf
                <input name="title" type="text" maxlength="150" placeholder="Judul lampiran (opsional)"
                       class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="file" type="file" required
                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    <x-ui.icon name="paper-clip" class="h-3.5 w-3.5" />
                    Unggah lampiran
                </button>
            </form>
        </x-ui.card>
    </div>

    <x-ui.card class="mt-6" padded="false" title="Pengumpulan siswa">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Siswa</th>
                        <th class="px-5 py-3">Percobaan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Dikumpulkan</th>
                        <th class="px-5 py-3">Berkas</th>
                        <th class="px-5 py-3">Nilai</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($submissions as $submission)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-900">{{ $submission->student?->name }}</p>
                                @if ($submission->content)
                                    <p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $submission->content }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">#{{ $submission->attempt_no }}</td>
                            <td class="px-5 py-3">
                                <x-ui.badge :color="$submission->status->color()">{{ $submission->status->label() }}</x-ui.badge>
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $submission->submitted_at?->translatedFormat('d M Y H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <ul class="space-y-1">
                                    @forelse ($submission->files as $file)
                                        <li>
                                            <a href="{{ route('submission-files.download', $file) }}"
                                               class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                                <x-ui.icon name="download" class="h-3 w-3" />
                                                {{ \Illuminate\Support\Str::limit($file->original_name, 24) }}
                                            </a>
                                        </li>
                                    @empty
                                        <span class="text-xs text-slate-400">—</span>
                                    @endforelse
                                </ul>
                            </td>
                            <td class="px-5 py-3 text-slate-700">
                                @if ($submission->grade)
                                    <span class="font-semibold">{{ $submission->grade->score }}</span>
                                    <span class="text-xs text-slate-400">/ {{ $assignment->max_score }}</span>
                                @else
                                    <span class="text-xs text-slate-400">Belum dinilai</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <details class="relative">
                                    <summary class="cursor-pointer text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                        {{ $submission->grade ? 'Ubah nilai' : 'Beri nilai' }}
                                    </summary>
                                    <form method="POST" action="{{ route('teacher.submissions.grade', $submission) }}"
                                          class="mt-2 w-56 space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600">Skor (0–{{ $assignment->max_score }})</label>
                                            <input name="score" type="number" step="0.01" min="0" max="{{ $assignment->max_score }}"
                                                   value="{{ $submission->grade?->score }}" required
                                                   class="mt-1 block w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600">Umpan balik</label>
                                            <textarea name="feedback" rows="3"
                                                      class="mt-1 block w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">{{ $submission->grade?->feedback }}</textarea>
                                        </div>
                                        <button type="submit"
                                                class="w-full rounded-lg bg-indigo-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                            Simpan nilai
                                        </button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada siswa yang mengumpulkan tugas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.portal>
