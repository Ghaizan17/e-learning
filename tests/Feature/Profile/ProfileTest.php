<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->student()->create([
        'name' => 'Nama Lama',
        'email' => 'lama@school.test',
        'password' => 'password-lama',
    ]);
});

it('updates the profile information', function (): void {
    $this->actingAs($this->user)
        ->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@school.test',
            'phone' => '081234567890',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasNoErrors();

    $this->user->refresh();

    expect($this->user->name)->toBe('Nama Baru')
        ->and($this->user->email)->toBe('baru@school.test')
        ->and($this->user->phone)->toBe('081234567890')
        ->and($this->user->email_verified_at)->toBeNull();
});

it('keeps the email unique across accounts', function (): void {
    $other = User::factory()->create(['email' => 'dipakai@school.test']);

    $this->actingAs($this->user)
        ->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => $other->email,
        ])
        ->assertSessionHasErrors('email');

    expect($this->user->fresh()->email)->toBe('lama@school.test');
});

it('stores an uploaded avatar on the public disk', function (): void {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('profile.update'), [
            '_method' => 'PATCH',
            'name' => 'Nama Baru',
            'email' => 'baru@school.test',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasNoErrors();

    $path = $this->user->fresh()->avatar_path;

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
});

it('changes the password when the current password is correct', function (): void {
    $this->actingAs($this->user)
        ->put(route('profile.password'), [
            'current_password' => 'password-lama',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasNoErrors();

    expect(Hash::check('password-baru-123', $this->user->fresh()->password))->toBeTrue();
});

it('rejects a password change when the current password is wrong', function (): void {
    $this->actingAs($this->user)
        ->put(route('profile.password'), [
            'current_password' => 'salah',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password-lama', $this->user->fresh()->password))->toBeTrue();
});
