<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RootUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['name' => 'root'],
            [
                'name' => 'root',
                'password' => Hash::make('root'),
            ]);

        $user->assignRole(UserRole::Admin->value);
    }
}
