<?php

namespace Database\Factories;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the account is deactivated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->withRole(RoleName::SuperAdmin);
    }

    public function schoolAdmin(): static
    {
        return $this->withRole(RoleName::SchoolAdmin);
    }

    public function teacher(): static
    {
        return $this->withRole(RoleName::Teacher);
    }

    public function homeroomTeacher(): static
    {
        return $this->withRole(RoleName::HomeroomTeacher);
    }

    public function student(): static
    {
        return $this->withRole(RoleName::Student);
    }

    /**
     * Assign a role once the user is persisted.
     */
    protected function withRole(RoleName $role): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole($role->value));
    }
}
