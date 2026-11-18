{{-- Compact metric tile. Use real values only. --}}
@props(['label', 'value', 'hint' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        @if ($icon)
            <span class="text-slate-400">{!! $icon !!}</span>
        @endif
    </div>
    <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
