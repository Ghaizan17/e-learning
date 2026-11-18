{{-- Progress bar, $value is an integer 0-100. --}}
@props(['value' => 0, 'label' => null])

@php($percent = max(0, min(100, (int) $value)))

<div {{ $attributes }}>
    @if ($label)
        <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
            <span>{{ $label }}</span>
            <span class="font-medium text-slate-700">{{ $percent }}%</span>
        </div>
    @endif
    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200"
         role="progressbar"
         aria-valuenow="{{ $percent }}"
         aria-valuemin="0"
         aria-valuemax="100">
        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $percent }}%"></div>
    </div>
</div>
