<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::role(UserRole::Customer->value)->inRandomOrder()->limit(2)->pluck('id');

        return [
            'amount' => fake()->randomFloat(1, 0.2, 10),
            'from_user_id' => $users[0],
            'to_user_id' => $users[1],
        ];
    }
}
