<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 text-slate-900 antialiased">
    <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            <div class="mb-6 flex items-center justify-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">E</span>
                <span class="text-base font-semibold tracking-tight">{{ config('app.name') }}</span>
            </div>

            {{ $slot }}
        </div>
    </main>

    <footer class="pb-6 text-center text-xs text-slate-400">
        &copy; {{ now()->year }} {{ config('app.name') }}
    </footer>
</body>
</html>
