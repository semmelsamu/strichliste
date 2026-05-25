<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\BuyArticleTransaction;
use App\Models\UndoTransaction;
use App\Models\User;
use Database\Factories\DepositTransactionFactory;
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
            'name' => 'Kasse K032',
            'type' => UserType::World,
        ]);

        User::factory()->create([
            'name' => 'FSIM',
            'type' => UserType::Vendor,
        ]);

        User::factory(10)->create();

        $this->call(CategorySeeder::class);
        $this->call(ArticleSeeder::class);

        DepositTransactionFactory::new()->count(30)->create();
        BuyArticleTransaction::factory(30)->create();
        UndoTransaction::factory(10)->create();
    }
}
