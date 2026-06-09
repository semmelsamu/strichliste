<?php

namespace Database\Seeders;

use App\Enums\UserType;
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
        User::firstOrCreate(
            ['name' => 'root'],
            [
                'name' => 'root',
                'password' => Hash::make('root'),
                'type' => UserType::NormalUser,
            ]);
    }
}
