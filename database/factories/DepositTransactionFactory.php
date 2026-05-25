<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class DepositTransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->randomNumber(1, 10),
            'from_user_id' => User::where('type', UserType::World)->inRandomOrder()->value('id'),
            'to_user_id' => User::where('type', UserType::NormalUser)->inRandomOrder()->value('id'),
        ];
    }
}
