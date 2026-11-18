<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\ClassroomOverview;
use App\Filament\Widgets\EnrollmentChart;
use App\Filament\Widgets\LatestCourses;
use App\Filament\Widgets\RecentUsers;
use App\Filament\Widgets\RoleDistributionChart;
use App\Models\User;
use Livewire\Livewire;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make();
    $this->admin = User::factory()->schoolAdmin()->create();
    $this->actingAs($this->admin);
});

it('uses a three-column dashboard grid', function (): void {
    expect((new Dashboard)->getColumns())->toBe(3);
});

it('renders the dashboard widgets with real data', function (): void {
    Livewire::test(Dashboard::class)->assertOk();

    Livewire::test(LatestCourses::class)
        ->assertOk()
        ->assertSee($this->fixture->course->title);

    Livewire::test(RecentUsers::class)
        ->assertOk()
        ->assertSee($this->fixture->teacher->name);

    Livewire::test(ClassroomOverview::class)
        ->assertOk()
        ->assertSee($this->fixture->classroom->name);

    Livewire::test(RoleDistributionChart::class)->assertOk();
    Livewire::test(EnrollmentChart::class)->assertOk();
});
