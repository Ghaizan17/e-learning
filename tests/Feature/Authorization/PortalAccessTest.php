<?php

use App\Models\User;

it('redirects guests from the student and teacher portals to login', function (string $path): void {
    $this->get($path)->assertRedirect(route('login'));
})->with([
    'student' => '/student/dashboard',
    'teacher' => '/guru/dashboard',
    'homeroom teacher' => '/walikelas/dashboard',
]);

it('lets a student open the student dashboard', function (): void {
    $student = User::factory()->student()->create(['name' => 'Siti Rahma']);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Siti Rahma');
});

it('lets a teacher open the teacher dashboard', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('teacher.dashboard', ['portal' => 'guru']))
        ->assertOk();
});

it('lets a homeroom teacher open the teacher dashboard', function (): void {
    $teacher = User::factory()->homeroomTeacher()->create();

    $this->actingAs($teacher)
        ->get(route('teacher.dashboard', ['portal' => 'walikelas']))
        ->assertOk()
        ->assertSee('Wali Kelas');
});

it('rejects the wrong portal prefix for the signed-in role', function (): void {
    $this->actingAs(User::factory()->teacher()->create())
        ->get('/walikelas/dashboard')
        ->assertNotFound();

    $this->actingAs(User::factory()->homeroomTeacher()->create())
        ->get('/guru/dashboard')
        ->assertNotFound();
});

it('generates portal links with the matching role prefix', function (): void {
    $this->actingAs(User::factory()->teacher()->create())
        ->get('/guru/dashboard')
        ->assertOk()
        ->assertSee('/guru/courses', false);

    $this->actingAs(User::factory()->homeroomTeacher()->create())
        ->get('/walikelas/dashboard')
        ->assertOk()
        ->assertSee('/walikelas/courses', false);
});

it('uses the role prefix inside shared pages for teacher users', function (): void {
    $teacher = User::factory()->teacher()->create();
    $homeroom = User::factory()->homeroomTeacher()->create();

    $this->actingAs($teacher->fresh())
        ->get('/profile')
        ->assertOk()
        ->assertSee('/guru/courses', false);

    $this->actingAs($homeroom->fresh())
        ->get('/profile')
        ->assertOk()
        ->assertSee('/walikelas/courses', false);
});

it('redirects the legacy walikelas spelling', function (): void {
    $this->get('/walikkelas')->assertRedirect('/walikelas');
});

it('forbids a teacher from opening the student portal', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('student.dashboard'))
        ->assertForbidden();
});

it('forbids a student from opening the teacher portal', function (): void {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->get(route('teacher.dashboard', ['portal' => 'guru']))
        ->assertForbidden();
});

it('forbids an inactive user with a live session from opening a portal', function (): void {
    $student = User::factory()->student()->inactive()->create();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertForbidden();
});

it('only allows administrators into the filament panel', function (string $factoryState, int $status): void {
    $user = User::factory()->{$factoryState}()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertStatus($status);
})->with([
    'super admin' => ['superAdmin', 200],
    'school admin' => ['schoolAdmin', 200],
    'teacher' => ['teacher', 403],
    'homeroom teacher' => ['homeroomTeacher', 403],
    'student' => ['student', 403],
]);

it('forbids a deactivated admin from the filament panel', function (): void {
    $admin = User::factory()->superAdmin()->inactive()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertForbidden();
});

it('redirects the shared dashboard to the portal matching the role', function (): void {
    $this->actingAs(User::factory()->student()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('student.dashboard'));

    $this->actingAs(User::factory()->teacher()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('teacher.dashboard', ['portal' => 'guru']));

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('filament.admin.pages.dashboard'));
});

it('renders portal pages as a full document with vite assets', function (): void {
    $response = $this->actingAs(User::factory()->student()->create())
        ->get(route('student.dashboard'))
        ->assertOk();

    expect($response->getContent())
        ->toContain('<!DOCTYPE html>')
        ->toContain('<link rel="stylesheet"')
        ->toContain('<script type="module"')
        ->toContain('x-data="portalShell"')
        ->toContain('id="confirm-dialog-title"');
});
