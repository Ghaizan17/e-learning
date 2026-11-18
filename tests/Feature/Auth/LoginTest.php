<?php

use App\Models\User;

it('shows the login page to guests', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk ke akun Anda');
});

it('authenticates a super admin and redirects to the admin panel', function (): void {
    $user = User::factory()->superAdmin()->create(['password' => 'secret-password']);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ]);

    $response->assertRedirect(route('filament.admin.pages.dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('authenticates a school admin and redirects to the admin panel', function (): void {
    $user = User::factory()->schoolAdmin()->create(['password' => 'secret-password']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('filament.admin.pages.dashboard'));
});

it('authenticates a teacher and redirects to the teacher portal', function (): void {
    $user = User::factory()->teacher()->create(['password' => 'secret-password']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('teacher.dashboard'));
});

it('authenticates a homeroom teacher and redirects to the teacher portal', function (): void {
    $user = User::factory()->homeroomTeacher()->create(['password' => 'secret-password']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('teacher.dashboard'));
});

it('authenticates a student and redirects to the student portal', function (): void {
    $user = User::factory()->student()->create(['password' => 'secret-password']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('student.dashboard'));
});

it('rejects invalid credentials without authenticating', function (): void {
    $user = User::factory()->student()->create(['password' => 'secret-password']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects an inactive account and keeps the session guest', function (): void {
    $user = User::factory()->student()->inactive()->create(['password' => 'secret-password']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('throttles repeated failed login attempts', function (): void {
    $user = User::factory()->student()->create(['password' => 'secret-password']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs the user out', function (): void {
    $user = User::factory()->student()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
