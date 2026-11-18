{{-- Empty state for lists without records. --}}
@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50/60 px-6 py-12 text-center']) }}>
    <svg class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M4.5 10.5v9h15v-9M9 19.5v-6h6v6" />
    </svg>
    <p class="text-sm font-semibold text-slate-900">{{ $title }}</p>
    @if ($description)
        <p class="max-w-md text-sm text-slate-500">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
