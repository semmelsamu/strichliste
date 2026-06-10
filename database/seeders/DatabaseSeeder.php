<?php

namespace Database\Seeders;

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
        $this->call(UserRoleSeeder::class);
        $this->call(RootUserSeeder::class);
        $this->call(BaseWorldVendorSeeder::class);

        if (! app()->isProduction()) {
            $this->call(CategorySeeder::class);
            $this->call(ArticleSeeder::class);

            $this->call(UserSeeder::class);

            $this->call(TransactionSeeder::class);
        }
    }
}
