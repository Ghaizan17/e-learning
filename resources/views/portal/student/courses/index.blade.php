@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-layouts.portal title="Kursus Saya">
    <x-ui.page-header
        title="Kursus Saya"
        description="Lanjutkan pembelajaran dari kursus yang Anda ikuti." />

    @if ($courses->isEmpty())
        <x-ui.empty-state
            title="Belum ada kursus"
            description="Kursus akan muncul setelah guru menerbitkannya untuk kelas Anda." />
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($courses as $item)
                <x-ui.card padded="false" class="flex h-full flex-col overflow-hidden">
                    <div class="aspect-[16/9] w-full bg-slate-100">
                        @if ($item['course']->thumbnail_path)
                            <img src="{{ Storage::disk('public')->url($item['course']->thumbnail_path) }}"
                                 alt="Sampul {{ $item['course']->title }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-slate-300">
                                <x-ui.icon name="book" class="h-10 w-10" />
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col px-5 py-4">
                        <p class="text-sm font-semibold leading-snug text-slate-900">{{ $item['course']->title }}</p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $item['course']->teacherAssignment?->subject?->name }} &middot;
                            {{ $item['course']->teacherAssignment?->classroom?->name }}
                        </p>
                        <div class="mt-3">
                            <x-ui.progress :value="$item['percent']" label="Progres" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ $item['percent'] }}% selesai
                            &middot;
                            {{ $item['enrollment']->status->label() }}
                        </p>
                    </div>
                    <div class="mt-auto border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('student.courses.show', $item['course']) }}"
                           class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                            Buka kursus
                            <x-ui.icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
