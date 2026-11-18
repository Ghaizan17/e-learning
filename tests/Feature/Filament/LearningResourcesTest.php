<?php

use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\RelationManagers\ChaptersRelationManager;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\User;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 1);
    $this->admin = User::factory()->superAdmin()->create();
});

it('renders the course and lesson resources', function (string $path): void {
    $this->actingAs($this->admin)->get($path)->assertOk();
})->with([
    '/admin/courses',
    '/admin/courses/create',
    '/admin/lessons',
    '/admin/lessons/create',
]);

it('lists the seeded course title', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/courses')
        ->assertOk()
        ->assertSee('Pemrograman Web Dasar');
});

it('creates a course through the admin page with a generated slug', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(CreateCourse::class)
        ->fillForm([
            'teacher_assignment_id' => $this->fixture->assignment->getKey(),
            'title' => 'Jaringan Komputer',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $course = Course::query()->where('title', 'Jaringan Komputer')->first();

    expect($course)->not->toBeNull()
        ->and($course->slug)->toBe('jaringan-komputer');
});

it('adds chapters with sequential positions from the relation manager', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(ChaptersRelationManager::class, [
        'ownerRecord' => $this->fixture->course,
        'pageClass' => EditCourse::class,
    ])
        ->callTableAction('create', data: [
            'title' => 'Bab Kedua',
            'description' => 'Materi lanjutan.',
        ])
        ->assertHasNoTableActionErrors();

    $chapter = Chapter::query()->where('title', 'Bab Kedua')->first();

    expect($chapter)->not->toBeNull()
        ->and($chapter->position)->toBe(2)
        ->and($chapter->is_published)->toBeFalse();
});
