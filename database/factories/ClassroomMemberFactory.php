<?php

namespace Database\Factories;

use App\Enums\ClassroomMemberStatus;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassroomMember>
 */
class ClassroomMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            'user_id' => User::factory()->student(),
            'joined_at' => now()->startOfYear()->toDateString(),
            'left_at' => null,
            'status' => ClassroomMemberStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'left_at' => now()->toDateString(),
            'status' => ClassroomMemberStatus::Dropped,
        ]);
    }
}
