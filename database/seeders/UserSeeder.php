<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
    }
}
