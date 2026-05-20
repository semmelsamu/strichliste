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
        // Must pick a transaction that has NOT already been undone
        $undoneTransaction = Transaction::whereDoesntHave('undoTransaction')
            ->inRandomOrder();

        return [
            'transaction_id' => Transaction::factory(),
            'undone_transaction_id' => $undoneTransaction->value('id'),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (UndoTransaction $undoTransaction) {
            $undoneTransaction = Transaction::find($undoTransaction->undone_transaction_id);

            $undoTransaction->transaction->update([
                'amount' => -$undoneTransaction->amount,
                'from_user_id' => $undoneTransaction->fromUser->id,
                'to_user_id' => $undoneTransaction->toUser->id,
            ]);
        });
    }
}
