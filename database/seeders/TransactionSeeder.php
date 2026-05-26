<?php

namespace Database\Seeders;

use App\Models\BuyArticleTransaction;
use App\Models\UndoTransaction;
use Database\Factories\DepositTransactionFactory;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DepositTransactionFactory::new()->count(20)->create();
        $this->createUndoTransactions(5);
        BuyArticleTransaction::factory(30)->create();
        $this->createUndoTransactions(5);
        DepositTransactionFactory::new()->count(5)->create();
        $this->createUndoTransactions(5);
        BuyArticleTransaction::factory(20)->create();
        $this->createUndoTransactions(5);
    }

    private function createUndoTransactions(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            UndoTransaction::factory()->create();
        }
    }
}
