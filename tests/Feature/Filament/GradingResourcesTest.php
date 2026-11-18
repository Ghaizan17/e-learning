<?php

use App\Filament\Resources\Grades\Pages\ListGrades;
use App\Filament\Resources\Grades\Pages\ViewGrade;
use App\Models\GradeHistory;
use App\Models\User;
use Tests\Support\GradingFixture;

beforeEach(function (): void {
    $this->fixture = GradingFixture::make();
    $this->admin = User::factory()->schoolAdmin()->create();
    $this->grade = $this->fixture->grade($this->fixture->assignmentComponent, $this->fixture->students[0], 75);
});

it('renders grading pages for a school admin', function (string $path): void {
    $this->actingAs($this->admin)->get($path)->assertOk();
})->with([
    '/admin/grading-categories',
    '/admin/grading-categories/create',
    '/admin/grades',
]);

it('renders the grade view page with history relation manager', function (): void {
    $this->actingAs($this->admin)
        ->get("/admin/grades/{$this->grade->getKey()}")
        ->assertOk();

    $this->actingAs($this->admin);

    Livewire\Livewire::test(ViewGrade::class, ['record' => $this->grade->getKey()])
        ->assertOk();
});

it('edits a grade through the table action and records history', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(ListGrades::class)
        ->callTableAction('editScore', $this->grade->getKey(), data: [
            'score' => 88,
            'reason' => 'Penilaian ulang',
            'is_published' => true,
        ])
        ->assertHasNoTableActionErrors();

    expect((float) $this->grade->fresh()->score)->toBe(88.0)
        ->and(GradeHistory::query()->where('grade_id', $this->grade->getKey())->count())->toBe(1);
});
