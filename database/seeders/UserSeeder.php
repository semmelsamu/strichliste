<?php

namespace Database\Seeders;

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
        $user = User::firstOrCreate(
            ['name' => 'tally_user'],
            [
                'password' => Hash::make('password'),
            ]);

        User::factory()->tally_user()->count(10)->create();
    }
}
