@props(['title' => null])

@php
    $user = auth()->user();
    $roleLabel = $user?->primaryRole()?->label();

    $sections = [];

    if ($user?->isStudent()) {
        $sections = [
            ['label' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'student.dashboard', 'pattern' => 'student.dashboard', 'icon' => 'home'],
                ['label' => 'Kursus Saya', 'route' => 'student.courses.index', 'pattern' => 'student.courses.*', 'icon' => 'book'],
                ['label' => 'Tugas', 'route' => 'student.assignments.index', 'pattern' => 'student.assignments.*', 'icon' => 'clipboard'],
                ['label' => 'Quiz & Ujian', 'route' => 'student.quizzes.index', 'pattern' => 'student.quizzes.*', 'icon' => 'cap'],
                ['label' => 'Nilai', 'route' => 'student.grades.index', 'pattern' => 'student.grades.*', 'icon' => 'chart'],
                ['label' => 'Kehadiran', 'route' => 'student.attendance.index', 'pattern' => 'student.attendance.*', 'icon' => 'calendar'],
                ['label' => 'Pengumuman', 'route' => 'student.announcements.index', 'pattern' => 'student.announcements.*', 'icon' => 'megaphone'],
            ]],
        ];
    } elseif ($user?->isTeacher()) {
        $sections = [
            ['label' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'teacher.dashboard', 'pattern' => 'teacher.dashboard', 'icon' => 'home'],
                ['label' => 'Kelas Saya', 'route' => 'teacher.classes.index', 'pattern' => 'teacher.classes.*', 'icon' => 'users'],
                ['label' => 'Kursus', 'route' => 'teacher.courses.index', 'pattern' => 'teacher.courses.*', 'icon' => 'book'],
                ['label' => 'Tugas', 'route' => 'teacher.assignments.index', 'pattern' => 'teacher.assignments.*', 'icon' => 'clipboard'],
                ['label' => 'Quiz & Ujian', 'route' => 'teacher.quizzes.index', 'pattern' => 'teacher.quizzes.*', 'icon' => 'cap'],
                ['label' => 'Nilai', 'route' => 'teacher.grades.index', 'pattern' => 'teacher.grades.*', 'icon' => 'chart'],
                ['label' => 'Kehadiran', 'route' => 'teacher.attendance.index', 'pattern' => 'teacher.attendance.*', 'icon' => 'calendar'],
            ]],
        ];
    } elseif ($user?->isAdmin()) {
        $sections = [
            ['label' => null, 'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'home'],
                ['label' => 'Panel Admin', 'url' => '/admin', 'pattern' => 'filament.admin.*', 'icon' => 'chart'],
            ]],
        ];
    }

    $sections = array_values(array_filter(array_map(function (array $section): array {
        $section['items'] = array_values(array_filter(
            $section['items'],
            fn (array $item): bool => isset($item['url']) || \Illuminate\Support\Facades\Route::has($item['route']),
        ));

        return $section;
    }, $sections), fn (array $section): bool => $section['items'] !== []));

    $initials = collect(explode(' ', (string) $user?->name))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-50 lg:flex">
    <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"></div>

    <aside x-cloak
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        <div class="flex h-16 items-center gap-2 border-b border-slate-100 px-5">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">E</span>
            <span class="truncate text-sm font-semibold tracking-tight">{{ config('app.name') }}</span>
            <button type="button" @click="sidebarOpen = false" class="ml-auto rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 lg:hidden">
                <x-ui.icon name="x" class="h-5 w-5" />
                <span class="sr-only">Tutup menu</span>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4" aria-label="Navigasi utama">
            @foreach ($sections as $section)
                <div>
                    @if ($section['label'])
                        <p class="px-2 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $section['label'] }}</p>
                    @endif
                    <ul class="space-y-1">
                        @foreach ($section['items'] as $item)
                            @php
                                $active = request()->routeIs($item['pattern']);
                            @endphp
                            <li>
                                <a href="{{ isset($item['url']) ? url($item['url']) : route($item['route']) }}"
                                   @class([
                                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                       'bg-indigo-50 text-indigo-700' => $active,
                                       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
                                   ])
                                   @if ($active) aria-current="page" @endif>
                                    <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0" />
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-slate-100 p-3">
            <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">{{ $initials ?: '?' }}</span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-900">{{ $user?->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $roleLabel }}</p>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                <x-ui.icon name="user" class="h-5 w-5" />
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-700">
                    <x-ui.icon name="logout" class="h-5 w-5" />
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen flex-1 flex-col">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6">
            <button type="button" @click="sidebarOpen = true" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden">
                <x-ui.icon name="menu" class="h-5 w-5" />
                <span class="sr-only">Buka menu</span>
            </button>

            <p class="truncate text-sm font-semibold text-slate-900">{{ $title ?? 'Dashboard' }}</p>

            <div class="ml-auto flex items-center gap-3">
                @if ($roleLabel)
                    <x-ui.badge color="indigo">{{ $roleLabel }}</x-ui.badge>
                @endif

                @php
                    $recentNotifications = $user?->notifications()->latest()->limit(5)->get() ?? collect();
                    $unreadNotifications = $user?->unreadNotifications()->count() ?? 0;
                @endphp

                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = ! open" @keydown.escape.window="open = false"
                            class="relative rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Notifikasi">
                        <x-ui.icon name="bell" class="h-5 w-5" />
                        @if ($unreadNotifications > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </span>
                        @endif
                    </button>

                    <div x-cloak x-show="open" x-transition @click.outside="open = false"
                         class="absolute right-0 z-30 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Notifikasi</p>
                            @if ($unreadNotifications > 0)
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                        Tandai semua
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                            @forelse ($recentNotifications as $notification)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-3 text-left transition hover:bg-slate-50">
                                        <p class="flex items-center gap-2 text-sm font-medium {{ $notification->read_at ? 'text-slate-600' : 'text-slate-900' }}">
                                            @unless ($notification->read_at)
                                                <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-indigo-500"></span>
                                            @endunless
                                            {{ $notification->data['title'] ?? 'Notifikasi' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $notification->data['body'] ?? '' }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                    </button>
                                </form>
                            @empty
                                <p class="px-4 py-6 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <span class="hidden text-sm text-slate-600 sm:block">{{ $user?->name }}</span>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
            @endif

            @if ($errors->any())
                <x-ui.alert type="danger" class="mb-4">
                    <p class="font-medium">Periksa kembali isian berikut:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 px-6 py-4 text-xs text-slate-400">
            {{ config('app.name') }} &copy; {{ now()->year }}
        </footer>
    </div>
</div>
