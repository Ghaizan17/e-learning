@props(['name', 'class' => 'h-5 w-5'])

@php
    $paths = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.25 9v10.5h13.5V9" />',
        'book' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c-1.6-1.4-3.9-2-6.75-2v13c2.85 0 5.15.6 6.75 2 1.6-1.4 3.9-2 6.75-2v-13c-2.85 0-5.15.6-6.75 2Zm0 0v13" />',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6M8.25 6.75h7.5A1.5 1.5 0 0 1 17.25 8.25v10.5a1.5 1.5 0 0 1-1.5 1.5h-7.5a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5Zm1.5 6h4.5m-4.5 3.75h4.5" />',
        'cap' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75 12 5.25l7.5 4.5L12 14.25 4.5 9.75Zm0 0v5.25c0 1.5 3.36 3 7.5 3s7.5-1.5 7.5-3V9.75" />',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V12m5 7.5V6.75m5 12.75v-4.5m5 4.5v-9" />',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75v3M17.25 3.75v3M4.5 8.25h15M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Zm2.25 7.5 2.25 2.25 4.5-4.5" />',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75v4.5l10.5 4.5V5.25L4.5 9.75Zm0 0H3.75A1.5 1.5 0 0 0 2.25 11.25v1.5a1.5 1.5 0 0 0 1.5 1.5H4.5m13.5-4.5a3 3 0 0 1 0 4.5" />',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5v-1.5a3.75 3.75 0 0 0-3.75-3.75h-3A3.75 3.75 0 0 0 4.5 18v1.5M10.5 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm9 8.25v-1.5a3.75 3.75 0 0 0-2.813-3.63M15 5.34a3 3 0 0 1 0 5.82" />',
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5v-1.5a3.75 3.75 0 0 0-3.75-3.75h-.75a3.75 3.75 0 0 0-3.75 3.75v1.5M12 11.25a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Z" />',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V6.75a1.5 1.5 0 0 0-1.5-1.5H6.75a1.5 1.5 0 0 0-1.5 1.5v11.25a1.5 1.5 0 0 0 1.5 1.5h7.5a1.5 1.5 0 0 0 1.5-1.5V15M18 12H9.75m8.25 0-3-3m3 3-3 3" />',
        'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />',
        'x' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />',
        'chevron-right' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9 5.25 6.75 6.75L9 18.75" />',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 4.5 4.5 10.5-10.5" />',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4.5 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
        'arrow-left' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />',
        'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3" />',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4.5-4.5M12 15l-4.5-4.5M4.5 19.5h15" />',
        'pencil' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.5 3.75 3.75 3.75L8.25 19.5H4.5v-3.75L16.5 3.75Zm0 0 2.25-2.25a1.06 1.06 0 0 1 1.5 0l2.25 2.25a1.06 1.06 0 0 1 0 1.5L20.25 7.5" />',
        'trash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M9 6.75V5.25A1.5 1.5 0 0 1 10.5 3.75h3A1.5 1.5 0 0 1 15 5.25v1.5m2.25 0v12a1.5 1.5 0 0 1-1.5 1.5h-7.5a1.5 1.5 0 0 1-1.5-1.5v-12M10.5 11.25v5.25m3-5.25v5.25" />',
        'paper-clip' => '<path stroke-linecap="round" stroke-linejoin="round" d="m18.75 12.75-6.364 6.364a4.5 4.5 0 1 1-6.364-6.364l7.5-7.5a3 3 0 0 1 4.243 4.243l-7.5 7.5a1.5 1.5 0 0 1-2.121-2.121l6.364-6.364" />',
        'play' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5v15l13.5-7.5-13.5-7.5Z" />',
        'eye' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Zm9.75 2.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" />',
        'lock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 10.5V7.5a5.25 5.25 0 0 1 10.5 0v3m-12 0h13.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z" />',
    ];
@endphp

<svg {{ $attributes->merge(['class' => $class, 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.75', 'aria-hidden' => 'true']) }}>
    {!! $paths[$name] ?? $paths['chevron-right'] !!}
</svg>
