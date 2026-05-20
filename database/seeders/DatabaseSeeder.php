<?php

namespace Database\Seeders;

use App\Models\BuyArticleTransaction;
use App\Models\Transaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(10)->create();

        $this->call(CategorySeeder::class);
        $this->call(ArticleSeeder::class);

        Transaction::factory(30)->create();
        BuyArticleTransaction::factory(30)->create();
        UndoTransaction::factory(10)->create();
    }
}
