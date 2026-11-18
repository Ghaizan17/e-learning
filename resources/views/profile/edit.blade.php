<x-layouts.portal title="Profil">
    <x-ui.page-header title="Profil" description="Kelola informasi akun dan kata sandi Anda." />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Informasi akun" description="Nama dan email digunakan pada seluruh portal.">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-4">
                        <span class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-slate-200 text-lg font-semibold text-slate-700">
                            @if ($user->avatar_path)
                                <img src="{{ Storage::disk('public')->url($user->avatar_path) }}" alt="Foto {{ $user->name }}" class="h-full w-full object-cover">
                            @else
                                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                            @endif
                        </span>
                        <div class="flex-1">
                            <label for="avatar" class="block text-sm font-medium text-slate-700">Foto profil</label>
                            <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp"
                                   class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                            <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                            @error('avatar')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="name" label="Nama lengkap" :value="$user->name" required />
                        <x-ui.input name="email" label="Email" type="email" :value="$user->email" required />
                        <x-ui.input name="phone" label="Nomor telepon" :value="$user->phone" hint="Opsional, digunakan untuk kontak sekolah." />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            Simpan perubahan
                        </button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Ubah kata sandi" description="Gunakan minimal 8 karakter.">
                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-ui.input name="current_password" label="Kata sandi saat ini" type="password" required autocomplete="current-password" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="password" label="Kata sandi baru" type="password" required autocomplete="new-password" />
                        <x-ui.input name="password_confirmation" label="Ulangi kata sandi baru" type="password" required autocomplete="new-password" />
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
                            Ubah kata sandi
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <x-ui.card title="Detail akun">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Peran</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $user->getRoleNames()->map(fn ($role) => \App\Enums\RoleName::tryFrom($role)?->label() ?? $role)->implode(', ') ?: 'Belum ada peran' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Status akun</dt>
                    <dd class="mt-1">
                        <x-ui.badge :color="$user->is_active ? 'success' : 'danger'">
                            {{ $user->is_active ? 'Aktif' : 'Tidak aktif' }}
                        </x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email terverifikasi</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $user->email_verified_at?->translatedFormat('d F Y') ?? 'Belum terverifikasi' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Bergabung</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $user->created_at?->translatedFormat('d F Y') }}</dd>
                </div>
            </dl>
        </x-ui.card>
    </div>
</x-layouts.portal>
