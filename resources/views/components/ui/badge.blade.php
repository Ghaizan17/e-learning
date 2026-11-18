@props(['color' => 'slate'])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.($colors[$color] ?? $colors['slate'])]) }}>
    {{ $slot }}
</span>
