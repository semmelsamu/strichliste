<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class BaseWorldVendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['name' => 'Kasse K032'],
            [
                'name' => 'Kasse K032',
                'type' => UserType::World,
            ]);

        User::firstOrCreate(
            ['name' => 'FSIM'],
            [
                'name' => 'FSIM',
                'type' => UserType::Vendor,
            ]);
    }
}
