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
        <x-ui.stat label="Kelas diampu" value="0" hint="Penugasan mengajar belum tersedia." />
        <x-ui.stat label="Kursus aktif" value="0" hint="Belum ada kursus yang diterbitkan." />
        <x-ui.stat label="Submission menunggu" value="0" hint="Tidak ada pekerjaan untuk dinilai." />
        <x-ui.stat label="Sesi mendatang" value="0" hint="Belum ada jadwal pertemuan." />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Kursus saya">
            <x-ui.empty-state
                title="Belum ada kursus"
                description="Kursus akan muncul setelah admin menetapkan penugasan mengajar untuk Anda." />
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
