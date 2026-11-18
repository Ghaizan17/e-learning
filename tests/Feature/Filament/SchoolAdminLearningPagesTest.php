<?php

use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;

beforeEach(function (): void {
    $this->admin = User::factory()->schoolAdmin()->create();
});

it('renders learning resource pages for a school admin', function (string $path): void {
    $this->actingAs($this->admin)
        ->get($path)
        ->assertOk();
})->with([
    '/admin/lessons',
    '/admin/lessons/create',
    '/admin/quizzes',
    '/admin/quizzes/create',
    '/admin/assignments',
    '/admin/assignments/create',
]);

it('allows a school admin to create a lesson without a parent chapter argument', function (): void {
    expect($this->admin->can('create', Lesson::class))->toBeTrue()
        ->and($this->admin->can('create', Quiz::class))->toBeTrue()
        ->and($this->admin->can('create', Assignment::class))->toBeTrue();
});
