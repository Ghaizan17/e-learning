<?php

use App\Services\Content\HtmlSanitizer;
use App\Services\Learning\LessonService;
use Tests\Support\LearningFixture;

it('keeps formatting but strips unsafe markup', function (): void {
    $html = <<<'HTML'
<h2>Judul materi</h2>
<p>Halo <strong>dunia</strong> <em>miring</em>.</p>
<script>alert('xss')</script>
<style>body{display:none}</style>
<iframe src="https://evil.test"></iframe>
<p onclick="alert(1)"><a href="javascript:alert(1)">klik</a>
<a href="https://contoh.test" target="_blank">aman</a></p>
<font color="red">teks tanpa tag font</font>
HTML;

    $clean = app(HtmlSanitizer::class)->sanitize($html);

    expect($clean)
        ->toContain('<h2>Judul materi</h2>')
        ->toContain('<strong>dunia</strong>')
        ->toContain('<em>miring</em>')
        ->toContain('teks tanpa tag font')
        ->toContain('href="https://contoh.test"')
        ->toContain('rel="noopener noreferrer"')
        ->not->toContain('<script')
        ->not->toContain('alert')
        ->not->toContain('<style')
        ->not->toContain('<iframe')
        ->not->toContain('javascript:')
        ->not->toContain('onclick')
        ->not->toContain('<font');
});

it('returns null for empty or whitespace-only content', function (): void {
    $sanitizer = app(HtmlSanitizer::class);

    expect($sanitizer->sanitize(null))->toBeNull()
        ->and($sanitizer->sanitize('   '))->toBeNull();
});

it('sanitizes lesson content when created and updated through the service', function (): void {
    $fixture = LearningFixture::make();
    $service = app(LessonService::class);

    $created = $service->create($fixture->chapter, [
        'title' => 'Materi Aman',
        'content_html' => '<p>Isi <strong>tebal</strong><img src=x onerror="alert(1)"></p>',
    ]);

    expect($created->content_html)
        ->toContain('<strong>tebal</strong>')
        ->not->toContain('<img')
        ->not->toContain('onerror');

    $service->update($created, [
        'content_html' => '<h2>Baru</h2><script>alert(1)</script>',
    ]);

    expect($created->fresh()->content_html)
        ->toBe('<h2>Baru</h2>');
});

it('renders the rich editor in the teacher course page and stores sanitized content', function (): void {
    $fixture = LearningFixture::make();

    $this->actingAs($fixture->teacher)
        ->get("/guru/courses/{$fixture->course->getKey()}")
        ->assertOk()
        ->assertSee('richEditor')
        ->assertSee('Konten materi')
        ->assertDontSee('Konten HTML');

    $this->actingAs($fixture->teacher)
        ->patch("/guru/lessons/{$fixture->lesson->getKey()}", [
            'title' => $fixture->lesson->title,
            'type' => $fixture->lesson->type->value,
            'content_html' => '<h2>Bab HTML</h2><p>Belajar <strong>tag</strong>.</p><script>alert(1)</script>',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($fixture->lesson->fresh()->content_html)
        ->toContain('<h2>Bab HTML</h2>')
        ->toContain('<strong>tag</strong>')
        ->not->toContain('<script');
});

it('renders sanitized rich content on the student lesson page', function (): void {
    $fixture = LearningFixture::make();
    $fixture->publishAll();

    app(LessonService::class)->update($fixture->lesson, [
        'content_html' => '<h2>Struktur HTML</h2><p>Gunakan <strong>tag</strong> yang benar.</p>',
    ]);

    $this->actingAs($fixture->students[0])
        ->get("/student/courses/{$fixture->course->getKey()}/lessons/{$fixture->lesson->getKey()}")
        ->assertOk()
        ->assertSee('<h2>Struktur HTML</h2>', false)
        ->assertSee('<strong>tag</strong>', false)
        ->assertSee('rich-content', false);
});
