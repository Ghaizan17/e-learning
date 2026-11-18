<x-layouts.guest title="Masuk">
    <x-ui.card>
        <div class="mb-5">
            <h1 class="text-lg font-semibold tracking-tight text-slate-900">Masuk ke akun Anda</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan email dan kata sandi yang diberikan sekolah.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <x-ui.input name="email" label="Email" type="email" required autofocus autocomplete="username" />
            <x-ui.input name="password" label="Kata sandi" type="password" required autocomplete="current-password" />

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Ingat saya
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                Masuk
            </button>
        </form>
    </x-ui.card>

    @if (app()->isLocal())
        <x-ui.card class="mt-4" title="Akun demo (lingkungan lokal)" description="Dibuat oleh AdminUserSeeder.">
            <ul class="space-y-1 text-sm text-slate-600">
                <li><span class="font-medium text-slate-900">Super Admin</span> — superadmin@school.test</li>
                <li><span class="font-medium text-slate-900">Admin Sekolah</span> — admin@school.test</li>
                <li><span class="font-medium text-slate-900">Guru</span> — guru@school.test</li>
                <li><span class="font-medium text-slate-900">Wali Kelas</span> — walikelas@school.test</li>
                <li><span class="font-medium text-slate-900">Siswa</span> — siswa@school.test</li>
            </ul>
            <p class="mt-2 text-xs text-slate-500">Kata sandi diambil dari <code>SEED_PASSWORD</code> (default: <code>password</code>).</p>
        </x-ui.card>
    @endif
</x-layouts.guest>
