<x-layouts.portal :title="'Siswa - '.$course->title">
    <x-ui.page-header
        :title="'Siswa - '.$course->title"
        description="Pendaftaran siswa mengikuti keanggotaan kelas pada tahun ajaran kursus.">
        <x-slot:actions>
            <a href="{{ route('teacher.courses.show', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali ke kursus
            </a>
            <form method="POST" action="{{ route('teacher.courses.sync-enrollments', $course) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <x-ui.icon name="users" class="h-4 w-4" />
                    Sinkronkan siswa kelas
                </button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Siswa terdaftar" :value="$enrollments->count()" />
        <x-ui.stat label="Anggota kelas aktif" :value="$activeMembers->count()" />
        <x-ui.stat label="Kelas" :value="$course->teacherAssignment?->classroom?->name ?? '-'" />
    </div>

    @php
        $enrolledIds = $enrollments->pluck('user_id')->all();
        $notEnrolled = $activeMembers->reject(fn ($member) => in_array($member->user_id, $enrolledIds, true));
    @endphp

    <div class="mt-6">
        <x-ui.card title="Daftar siswa terdaftar" :padded="false">
            @if ($enrollments->isEmpty())
                <div class="p-5">
                    <x-ui.empty-state title="Belum ada siswa terdaftar"
                                      description="Terbitkan kursus atau tekan tombol sinkronkan untuk mendaftarkan anggota kelas." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Email</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Terdaftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($enrollments as $enrollment)
                                @php($color = match ($enrollment->status->value) {
                                    'active' => 'success',
                                    'completed' => 'indigo',
                                    default => 'danger',
                                })
                                <tr>
                                    <td class="px-5 py-3 font-medium text-slate-900">{{ $enrollment->user?->name ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $enrollment->user?->email ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        <x-ui.badge :color="$color">{{ $enrollment->status->label() }}</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ $enrollment->enrolled_at?->format('d M Y') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>

    @if ($notEnrolled->isNotEmpty())
        <div class="mt-4">
            <x-ui.card title="Anggota kelas yang belum terdaftar" description="Tekan sinkronkan untuk mendaftarkan mereka.">
                <ul class="flex flex-wrap gap-2">
                    @foreach ($notEnrolled as $member)
                        <li class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ $member->student?->name ?? 'Siswa #'.$member->user_id }}
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    @endif
</x-layouts.portal>
