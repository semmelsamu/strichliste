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
        UndoTransaction::factory(5)->create();
        BuyArticleTransaction::factory(30)->create();
        UndoTransaction::factory(5)->create();
        DepositTransactionFactory::new()->count(5)->create();
        UndoTransaction::factory(5)->create();
        BuyArticleTransaction::factory(20)->create();
        UndoTransaction::factory(5)->create();
    }
}
