<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }

    public function tally_user(): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole(UserRole::Customer));
    }
}
