<x-layouts.portal :title="'Nilai · '.$course->title">
    <x-ui.page-header :title="$course->title" description="Rincian nilai">
        <x-slot:actions>
            <a href="{{ route('student.grades.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Semua nilai
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Nilai akhir" :value="$breakdown['final_score'] ?? '—'" />
        <x-ui.stat label="Huruf" :value="$breakdown['letter_grade'] ?? '—'" />
        <x-ui.stat label="Kategori" :value="count($breakdown['categories'])" />
    </div>

    <div class="mt-6 space-y-4">
        @foreach ($breakdown['categories'] as $entry)
            <x-ui.card :title="$entry['category']->name->label()" :description="'Bobot '.$entry['category']->weight.'%'">
                <x-slot:actions>
                    <x-ui.badge :color="$entry['category']->name->color()">
                        Rata-rata {{ $entry['average'] ?? '—' }}
                    </x-ui.badge>
                </x-slot:actions>

                @if ($entry['grades']->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada nilai yang dipublikasikan pada kategori ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="px-2 py-2">Komponen</th>
                                    <th class="px-2 py-2">Skor</th>
                                    <th class="px-2 py-2">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($entry['grades'] as $grade)
                                    <tr>
                                        <td class="px-2 py-2 text-slate-700">{{ $grade->component?->title ?? 'Komponen' }}</td>
                                        <td class="px-2 py-2 text-slate-700">{{ $grade->score }} / {{ $grade->max_score }}</td>
                                        <td class="px-2 py-2 text-slate-700">{{ $grade->percentage }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.portal>
