{{-- Inline alert. Types: info, success, warning, danger. --}}
@props(['type' => 'info'])

@php
    $styles = [
        'info' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-900',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border px-4 py-3 text-sm '.($styles[$type] ?? $styles['info'])]) }} role="alert">
    {{ $slot }}
</div>
