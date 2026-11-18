@php
    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };
@endphp

<x-layouts.portal title="Dashboard Guru">
    <x-ui.page-header
        :title="$greeting.', '.$user->name"
        description="Pantau kelas dan pembelajaran yang Anda ampu.">
        <x-slot:actions>
            <x-ui.badge color="indigo">Guru</x-ui.badge>
            @if ($isHomeroom)
                <x-ui.badge color="warning">Wali Kelas</x-ui.badge>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Kelas diampu" :value="$stats['classes']" hint="Penugasan mengajar aktif." />
        <x-ui.stat label="Kursus terbit" :value="$stats['publishedCourses']" :hint="$stats['draftCourses'].' kursus masih draf'" />
        <x-ui.stat label="Siswa terdaftar" :value="$stats['students']" hint="Di seluruh kursus Anda." />
        <x-ui.stat label="Kursus" :value="$stats['publishedCourses'] + $stats['draftCourses']" hint="Terbit dan draf." />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Kursus saya">
            @if ($courses->isEmpty())
                <x-ui.empty-state
                    title="Belum ada kursus"
                    description="Buat kursus dari penugasan mengajar Anda untuk mulai menyusun materi.">
                    <x-slot:action>
                        <a href="{{ route('teacher.courses.create') }}"
                           class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Buat kursus
                        </a>
                    </x-slot:action>
                </x-ui.empty-state>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($courses as $course)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('teacher.courses.show', $course) }}"
                                   class="truncate text-sm font-medium text-slate-900 hover:text-indigo-600">
                                    {{ $course->title }}
                                </a>
                                <p class="text-xs text-slate-500">
                                    {{ $course->teacherAssignment?->classroom?->name }}
                                    &middot; {{ $course->teacherAssignment?->subject?->name }}
                                    &middot; {{ $course->chapters_count }} bab
                                    &middot; {{ $course->enrollments_count }} siswa
                                </p>
                            </div>
                            <x-ui.badge :color="$course->status->value === 'published' ? 'success' : 'slate'">
                                {{ $course->status->label() }}
                            </x-ui.badge>
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
