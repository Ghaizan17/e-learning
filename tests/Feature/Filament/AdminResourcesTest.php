<?php

use App\Models\User;

it('renders the user management table with roles', function (): void {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->teacher()->create(['name' => 'Bu Rina']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Bu Rina')
        ->assertSee('Guru');
});

it('renders the create user form', function (): void {
    $admin = User::factory()->schoolAdmin()->create();

    $this->actingAs($admin)
        ->get('/admin/users/create')
        ->assertOk()
        ->assertSee('Nama lengkap')
        ->assertSee('Peran');
});
