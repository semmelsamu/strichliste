<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(ArticleSeeder::class);

        $this->call(BaseWorldVendorSeeder::class);

        $this->call(UserSeeder::class);

        $this->call(TransactionSeeder::class);
    }
}
