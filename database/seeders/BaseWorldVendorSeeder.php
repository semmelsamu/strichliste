<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class BaseWorldVendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $world = User::firstOrCreate(
            ['name' => 'Kasse K032'],
            [
                'name' => 'Kasse K032',
            ]);

        $world->assignRole(UserRole::World->value);

        $vendor = User::firstOrCreate(
            ['name' => 'FSIM'],
            [
                'name' => 'FSIM',
            ]);

        $vendor->assignRole(UserRole::Vendor->value);
    }
}
