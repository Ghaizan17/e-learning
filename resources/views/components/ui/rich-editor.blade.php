@props([
    'name' => 'content_html',
    'value' => '',
    'label' => 'Konten materi',
    'placeholder' => 'Tulis materi di sini…',
    'hint' => null,
])

@php
    $id = 'rich-editor-'.str_replace(['[', ']', '_'], '-', $name).'-'.uniqid();
    $content = app(\App\Services\Content\HtmlSanitizer::class)->sanitize((string) old($name, $value)) ?? '';
@endphp

<div
    class="space-y-1.5"
    x-data="richEditor({ initial: @js($content), name: @js($name) })"
>
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>

    <div class="rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-200">
        <div class="flex flex-wrap items-center gap-0.5 border-b border-slate-200 bg-slate-50 px-2 py-1.5" role="toolbar" aria-label="Pemformatan teks">
            <button type="button" @mousedown.prevent="format('bold')" title="Tebal" aria-label="Tebal"
                    class="rounded px-2 py-1 text-sm font-bold text-slate-700 hover:bg-white hover:shadow-sm">B</button>
            <button type="button" @mousedown.prevent="format('italic')" title="Miring" aria-label="Miring"
                    class="rounded px-2 py-1 text-sm italic text-slate-700 hover:bg-white hover:shadow-sm">I</button>
            <button type="button" @mousedown.prevent="format('underline')" title="Garis bawah" aria-label="Garis bawah"
                    class="rounded px-2 py-1 text-sm underline text-slate-700 hover:bg-white hover:shadow-sm">U</button>
            <button type="button" @mousedown.prevent="format('strikeThrough')" title="Coret" aria-label="Coret"
                    class="rounded px-2 py-1 text-sm line-through text-slate-700 hover:bg-white hover:shadow-sm">S</button>

            <span class="mx-1 h-4 w-px bg-slate-300" aria-hidden="true"></span>

            <button type="button" @mousedown.prevent="block('h2')" title="Judul besar" aria-label="Judul besar"
                    class="rounded px-2 py-1 text-xs font-bold text-slate-700 hover:bg-white hover:shadow-sm">H2</button>
            <button type="button" @mousedown.prevent="block('h3')" title="Judul kecil" aria-label="Judul kecil"
                    class="rounded px-2 py-1 text-xs font-bold text-slate-700 hover:bg-white hover:shadow-sm">H3</button>

            <span class="mx-1 h-4 w-px bg-slate-300" aria-hidden="true"></span>

            <button type="button" @mousedown.prevent="format('insertUnorderedList')" title="Daftar poin" aria-label="Daftar poin"
                    class="rounded px-2 py-1 text-sm text-slate-700 hover:bg-white hover:shadow-sm">•</button>
            <button type="button" @mousedown.prevent="format('insertOrderedList')" title="Daftar bernomor" aria-label="Daftar bernomor"
                    class="rounded px-2 py-1 text-sm text-slate-700 hover:bg-white hover:shadow-sm">1.</button>
            <button type="button" @mousedown.prevent="format('formatBlock', 'blockquote')" title="Kutipan" aria-label="Kutipan"
                    class="rounded px-2 py-1 text-sm text-slate-700 hover:bg-white hover:shadow-sm">❝</button>
            <button type="button" @mousedown.prevent="format('formatBlock', 'pre')" title="Blok kode" aria-label="Blok kode"
                    class="rounded px-2 py-1 font-mono text-xs text-slate-700 hover:bg-white hover:shadow-sm">&lt;/&gt;</button>

            <span class="mx-1 h-4 w-px bg-slate-300" aria-hidden="true"></span>

            <div class="relative">
                <button type="button" @mousedown.prevent="openLink" title="Tautan" aria-label="Tautan"
                        class="rounded px-2 py-1 text-sm text-slate-700 hover:bg-white hover:shadow-sm">🔗</button>

                <div x-show="linkOpen" x-cloak @keydown.escape.prevent="closeLink"
                     class="absolute left-0 top-full z-20 mt-1 w-64 rounded-lg border border-slate-200 bg-white p-2 shadow-lg">
                    <label class="block text-xs font-medium text-slate-600" :for="linkId">URL tautan</label>
                    <input x-ref="linkInput" :id="linkId" type="url" x-model="linkUrl"
                           @keydown.enter.prevent="applyLink"
                           class="mt-1 block w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                           placeholder="https://contoh.com">
                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button" @click="closeLink"
                                class="rounded-md px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="button" @click="applyLink"
                                class="rounded-md bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-700">Terapkan</button>
                    </div>
                </div>
            </div>

            <button type="button" @mousedown.prevent="format('removeFormat')" title="Hapus format" aria-label="Hapus format"
                    class="rounded px-2 py-1 text-xs text-slate-700 hover:bg-white hover:shadow-sm">Tx</button>

            <span class="mx-1 h-4 w-px bg-slate-300" aria-hidden="true"></span>

            <button type="button" @mousedown.prevent="format('undo')" title="Urungkan" aria-label="Urungkan"
                    class="rounded px-2 py-1 text-sm text-slate-700 hover:bg-white hover:shadow-sm">↶</button>
            <button type="button" @mousedown.prevent="format('redo')" title="Ulangi" aria-label="Ulangi"
                    class="rounded px-2 py-1 text-sm text-slate-700 hover:bg-white hover:shadow-sm">↷</button>
        </div>

        <div
            id="{{ $id }}"
            x-ref="editor"
            contenteditable="true"
            role="textbox"
            aria-multiline="true"
            data-placeholder="{{ $placeholder }}"
            @input="sync"
            @blur="sync"
            class="rich-content rich-editor min-h-40 max-h-96 overflow-y-auto px-3 py-2.5 outline-none"
        ></div>
    </div>

    <input type="hidden" name="{{ $name }}" x-ref="input" value="{{ $content }}">

    @if ($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>
