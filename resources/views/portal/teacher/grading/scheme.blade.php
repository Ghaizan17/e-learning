<x-layouts.portal :title="'Skema Nilai · '.$course->title">
    <x-ui.page-header :title="$course->title" description="Skema penilaian">
        <x-slot:actions>
            <a href="{{ route('teacher.grades.show', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Buku nilai
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card title="Bobot kategori" description="Total bobot harus tepat 100% agar kursus dapat diterbitkan.">
        <form method="POST" action="{{ route('teacher.grading.update', $course) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($categories as $category)
                    <div>
                        <label for="weight-{{ $category->name->value }}" class="block text-sm font-medium text-slate-700">
                            {{ $category->name->label() }}
                        </label>
                        <input id="weight-{{ $category->name->value }}" type="number" step="0.01" min="0" max="100"
                               name="weights[{{ $category->name->value }}]"
                               value="{{ old('weights.'.$category->name->value, $category->weight) }}"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('weights.'.$category->name->value)
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <p class="text-sm text-slate-500">
                    Total saat ini:
                    <span class="font-semibold {{ abs((float) $totalWeight - 100) < 0.01 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $totalWeight }}%
                    </span>
                </p>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    Simpan skema
                </button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.portal>
