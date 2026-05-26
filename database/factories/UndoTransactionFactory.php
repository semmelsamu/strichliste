<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\UndoTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UndoTransaction>
 */
class UndoTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $undoneTransaction = Transaction::whereNotIn('id', UndoTransaction::pluck('undone_transaction_id'))
            ->inRandomOrder()->first();

        return [
            'transaction_id' => Transaction::factory()->create([
                'amount' => -$undoneTransaction->amount,
                'from_user_id' => $undoneTransaction->fromUser->id,
                'to_user_id' => $undoneTransaction->toUser->id,
            ]),
            'undone_transaction_id' => $undoneTransaction->id,
        ];
    }
}
