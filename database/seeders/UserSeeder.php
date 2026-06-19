<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['name' => 'customer'],
            [
                'password' => Hash::make('password'),
            ]);
        $customer->assignRole(UserRole::Customer->value);

        $tallyHost = User::firstOrCreate(
            ['name' => 'tally_host'],
            [
                'password' => Hash::make('password'),
            ]);
        $tallyHost->assignRole(UserRole::TallyHost->value);

        User::factory()->tally_user()->count(10)->create();
    }
}
