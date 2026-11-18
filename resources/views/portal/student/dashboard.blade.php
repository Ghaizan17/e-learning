@php
    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
@endphp

<x-layouts.portal title="Dashboard Siswa">
    <x-ui.page-header
        :title="$greeting.', '.$user->name"
        description="Ringkasan pembelajaran Anda hari ini.">
        <x-slot:actions>
            <x-ui.badge color="indigo">Siswa</x-ui.badge>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Kursus diikuti" :value="$stats['courses']" hint="Kursus aktif dan selesai." />
        <x-ui.stat label="Materi selesai" :value="$stats['completedLessons']" :hint="$stats['inProgress'].' materi sedang dipelajari'" />
        <x-ui.stat label="Kursus selesai" :value="$stats['completedCourses']" hint="Seluruh materi tuntas." />
        <x-ui.stat label="Rata-rata progres" :value="$stats['averageProgress'].'%'" hint="Dari seluruh kursus." />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Kursus saya">
            @if ($courses->isEmpty())
                <x-ui.empty-state
                    title="Belum ada kursus"
                    description="Kursus akan muncul di sini setelah guru menerbitkan materi untuk kelas Anda." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($courses as $item)
                        <li class="py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <a href="{{ route('student.courses.show', $item['course']) }}"
                                       class="truncate text-sm font-medium text-slate-900 hover:text-indigo-600">
                                        {{ $item['course']->title }}
                                    </a>
                                    <p class="text-xs text-slate-500">
                                        {{ $item['course']->teacherAssignment?->subject?->name }}
                                        &middot; {{ $item['course']->teacherAssignment?->teacher?->name }}
                                    </p>
                                </div>
                                <x-ui.badge :color="$item['status']->value === 'completed' ? 'success' : 'indigo'">
                                    {{ $item['status']->label() }}
                                </x-ui.badge>
                            </div>
                            <div class="mt-2">
                                <x-ui.progress :value="$item['percent']" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card title="Profil singkat">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="mt-1 break-all font-medium text-slate-900">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Nomor telepon</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $user->phone ?? 'Belum diisi' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd class="mt-1">
                        <x-ui.badge :color="$user->is_active ? 'success' : 'danger'">
                            {{ $user->is_active ? 'Aktif' : 'Tidak aktif' }}
                        </x-ui.badge>
                    </dd>
                </div>
            </dl>
            <a href="{{ route('profile.edit') }}"
               class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Lengkapi profil
                <x-ui.icon name="chevron-right" class="h-4 w-4" />
            </a>
        </x-ui.card>
    </div>
</x-layouts.portal>
