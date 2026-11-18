@php
    use App\Enums\QuestionType;

    $totalPoints = $quiz->questions->sum(fn ($question) => (float) $question->score);
@endphp

<x-layouts.portal :title="$quiz->title">
    <x-ui.page-header :title="$quiz->title"
                      :description="$quiz->course?->title.' · '.$quiz->course?->teacherAssignment?->classroom?->name">
        <x-slot:actions>
            <x-ui.badge :color="$quiz->type->color()">{{ $quiz->type->label() }}</x-ui.badge>
            <x-ui.badge :color="$quiz->is_published ? 'success' : 'slate'">{{ $quiz->is_published ? 'Terbit' : 'Draf' }}</x-ui.badge>
            <a href="{{ route('teacher.quizzes.edit', $quiz) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="pencil" class="h-4 w-4" />
                Edit
            </a>
            <a href="{{ route('teacher.quizzes.index') }}"
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
        <x-ui.stat label="Soal" :value="$quiz->questions->count()" />
        <x-ui.stat label="Total poin" :value="$totalPoints" />
        <x-ui.stat label="Durasi" :value="$quiz->duration_minutes.' menit'" :hint="'maks '.$quiz->max_attempts.' percobaan'" />
        <x-ui.stat label="Percobaan" :value="$attempts->count()" :hint="$quiz->is_published ? 'Kuis terbit' : 'Masih draf'" />
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Pengaturan">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Jendela</dt><dd class="text-slate-800">{{ $quiz->starts_at?->translatedFormat('d M Y H:i') ?? 'Langsung' }} — {{ $quiz->ends_at?->translatedFormat('d M Y H:i') ?? 'Tanpa batas' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Nilai kelulusan</dt><dd class="text-slate-800">{{ $quiz->passing_score }}</dd></div>
                <div><dt class="text-xs text-slate-500">Acak soal / pilihan</dt><dd class="text-slate-800">{{ $quiz->shuffle_questions ? 'Ya' : 'Tidak' }} / {{ $quiz->shuffle_options ? 'Ya' : 'Tidak' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Hasil langsung</dt><dd class="text-slate-800">{{ $quiz->show_result_immediately ? 'Ya' : 'Setelah kuis berakhir' }}</dd></div>
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                <form method="POST" action="{{ route($quiz->is_published ? 'teacher.quizzes.unpublish' : 'teacher.quizzes.publish', $quiz) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold shadow-sm {{ $quiz->is_published ? 'border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                        <x-ui.icon name="check" class="h-4 w-4" />
                        {{ $quiz->is_published ? 'Sembunyikan dari siswa' : 'Terbitkan kuis' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('teacher.quizzes.destroy', $quiz) }}"
                      data-confirm="Hapus kuis ini beserta soal dan percobaannya?">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                        <x-ui.icon name="trash" class="h-4 w-4" />
                        Hapus
                    </button>
                </form>
            </div>
            @unless ($quiz->is_published)
                <p class="mt-2 text-xs text-slate-500">Kuis hanya bisa diterbitkan jika semua soal memiliki jawaban benar.</p>
            @endunless
        </x-ui.card>

        <x-ui.card title="Tambah soal">
            <form method="POST" action="{{ route('teacher.questions.store', $quiz) }}" class="space-y-4">
                @csrf
                @include('portal.teacher.quizzes._question-fields', ['question' => null])

                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Tambah soal
                </button>
            </form>
        </x-ui.card>
    </div>

    <div class="mt-6 space-y-3">
        <h2 class="text-sm font-semibold text-slate-900">Daftar soal</h2>

        @forelse ($quiz->questions as $question)
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-1 text-xs font-semibold text-slate-600">{{ $question->position }}</span>
                            <x-ui.badge color="slate">{{ $question->type->label() }}</x-ui.badge>
                            <span class="text-xs text-slate-500">{{ $question->score }} poin</span>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $question->question_text }}</p>

                        @if ($question->type->requiresOptions())
                            <ul class="mt-2 space-y-1 text-sm">
                                @foreach ($question->options as $option)
                                    <li class="{{ $option->is_correct ? 'font-medium text-emerald-700' : 'text-slate-600' }}">
                                        {{ $option->is_correct ? '✓' : '•' }} {{ $option->option_text }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($question->explanation)
                            <p class="mt-2 text-xs text-slate-500">Penjelasan: {{ $question->explanation }}</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('teacher.questions.destroy', $question) }}"
                          data-confirm="Hapus soal ini?">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                            <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                            Hapus
                        </button>
                    </form>
                </div>

                <details class="mt-3">
                    <summary class="cursor-pointer text-xs font-semibold text-indigo-600 hover:text-indigo-700">Edit soal</summary>
                    <form method="POST" action="{{ route('teacher.questions.update', $question) }}" class="mt-3 space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        @csrf
                        @method('PATCH')
                        @include('portal.teacher.quizzes._question-fields', ['question' => $question])
                        <button type="submit"
                                class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                            Simpan soal
                        </button>
                    </form>
                </details>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Belum ada soal" description="Tambahkan soal melalui formulir di atas." />
        @endforelse
    </div>

    <x-ui.card class="mt-6" padded="false" title="Percobaan siswa">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Siswa</th>
                        <th class="px-5 py-3">Percobaan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Mulai</th>
                        <th class="px-5 py-3">Dikumpulkan</th>
                        <th class="px-5 py-3">Skor</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($attempts as $attempt)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $attempt->student?->name }}</td>
                            <td class="px-5 py-3 text-slate-600">#{{ $attempt->attempt_no }}</td>
                            <td class="px-5 py-3"><x-ui.badge :color="$attempt->status->color()">{{ $attempt->status->label() }}</x-ui.badge></td>
                            <td class="px-5 py-3 text-slate-600">{{ $attempt->started_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $attempt->submitted_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $attempt->score ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('teacher.quiz-attempts.show', $attempt) }}"
                                   class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Tinjau</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada siswa yang mengerjakan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.portal>
