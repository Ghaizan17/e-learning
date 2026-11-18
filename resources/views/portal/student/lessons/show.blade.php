@php
    use App\Enums\LessonProgressStatus;
    use App\Enums\LessonType;

    $videoEmbed = null;

    if ($lesson->video_url) {
        $videoEmbed = preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([\w-]{11})~', $lesson->video_url, $matches)
            ? 'https://www.youtube.com/embed/'.$matches[1]
            : $lesson->video_url;
    }
@endphp

<x-layouts.portal :title="$lesson->title">
    <x-ui.page-header :title="$lesson->title" description="{{ $course->title }} &middot; {{ $lesson->chapter?->title }}">
        <x-slot:actions>
            <a href="{{ route('student.courses.show', $course) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <x-ui.icon name="arrow-left" class="h-4 w-4" />
                Kembali ke kursus
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <x-ui.card>
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <x-ui.badge color="indigo">{{ $lesson->type->label() }}</x-ui.badge>
                    @if ($lesson->estimated_duration_minutes)
                        <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                            <x-ui.icon name="clock" class="h-3.5 w-3.5" />
                            {{ $lesson->estimated_duration_minutes }} menit
                        </span>
                    @endif
                    @if ($progress->status === LessonProgressStatus::Completed)
                        <x-ui.badge color="success">Selesai</x-ui.badge>
                    @endif
                </div>

                @if ($lesson->type === LessonType::Video && $videoEmbed)
                    <div class="aspect-video w-full overflow-hidden rounded-lg bg-black">
                        <iframe src="{{ $videoEmbed }}" class="h-full w-full" allowfullscreen
                                title="Video materi {{ $lesson->title }}"></iframe>
                    </div>
                @elseif ($lesson->type === LessonType::Link && $lesson->external_url)
                    <a href="{{ $lesson->external_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Buka tautan materi
                        <x-ui.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @endif

                @if ($lesson->content_html)
                    <div @class([
                        'prose prose-slate max-w-none text-sm leading-relaxed',
                        'mt-4' => $lesson->type !== LessonType::Text,
                    ])>
                        {!! $lesson->content_html !!}
                    </div>
                @endif

                @if ($lesson->attachments->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <h3 class="text-sm font-semibold text-slate-900">Lampiran</h3>
                        <ul class="mt-2 space-y-2">
                            @foreach ($lesson->attachments as $attachment)
                                <li>
                                    <a href="{{ route('attachments.download', $attachment) }}"
                                       class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                        <x-ui.icon name="download" class="h-4 w-4" />
                                        {{ $attachment->title }}
                                        <span class="text-xs font-normal text-slate-400">({{ $attachment->humanSize() }})</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-ui.card>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    @if ($previous)
                        <a href="{{ route('student.lessons.show', [$course, $previous]) }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <x-ui.icon name="arrow-left" class="h-4 w-4" />
                            Sebelumnya
                        </a>
                    @endif
                    @if ($next)
                        <a href="{{ route('student.lessons.show', [$course, $next]) }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Berikutnya
                            <x-ui.icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @endif
                </div>

                @if ($progress->status !== LessonProgressStatus::Completed)
                    <form method="POST" action="{{ route('student.lessons.complete', $lesson) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            <x-ui.icon name="check" class="h-4 w-4" />
                            Tandai selesai
                        </button>
                    </form>
                @else
                    <x-ui.badge color="success">Materi ini sudah selesai</x-ui.badge>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Progres kursus">
                <x-ui.progress :value="$percent" />
                <p class="mt-2 text-sm text-slate-600">{{ $percent }}% selesai</p>
            </x-ui.card>

            <x-ui.card title="Navigasi materi">
                <ol class="space-y-1 text-sm">
                    @foreach ($course->chapters()->published()->with(['lessons' => fn ($query) => $query->where('is_published', true)])->get() as $chapter)
                        <li class="pt-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $chapter->title }}</li>
                        @foreach ($chapter->lessons as $item)
                            <li>
                                <a href="{{ route('student.lessons.show', [$course, $item]) }}"
                                   @class([
                                       'block rounded-lg px-2 py-1.5',
                                       'bg-indigo-50 font-medium text-indigo-700' => $item->getKey() === $lesson->getKey(),
                                       'text-slate-600 hover:bg-slate-50' => $item->getKey() !== $lesson->getKey(),
                                   ])>
                                    {{ $item->title }}
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ol>
            </x-ui.card>
        </div>
    </div>
</x-layouts.portal>
