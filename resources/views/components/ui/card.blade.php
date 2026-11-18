{{-- Card container. Pass $title / $description for a header, or use the default slot. --}}
@props(['title' => null, 'description' => null, 'padded' => true])

@php($bodyClass = $padded ? 'px-5 py-4' : '')

<section {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title)
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>
</section>
