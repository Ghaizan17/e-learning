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
        <x-ui.stat label="Kursus diikuti" value="0" hint="Belum ada kelas yang terhubung." />
        <x-ui.stat label="Tugas mendatang" value="0" hint="Tidak ada tenggat terdekat." />
        <x-ui.stat label="Rata-rata nilai" value="—" hint="Nilai muncul setelah penilaian." />
        <x-ui.stat label="Kehadiran" value="—" hint="Belum ada sesi pembelajaran." />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Kursus saya">
            <x-ui.empty-state
                title="Belum ada kursus"
                description="Kursus akan muncul di sini setelah admin memasukkan Anda ke kelas dan guru menerbitkan materi." />
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
