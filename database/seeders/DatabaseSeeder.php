<?php

namespace Database\Seeders;

use App\Enums\UserType;
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
        $this->call(TransactionSeeder::class);
    }
}
