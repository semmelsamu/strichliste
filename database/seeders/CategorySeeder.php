<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Softdrinks', 'icon' => 'lucide-cup-soda'],
            ['name' => 'Wasser und Säfte', 'icon' => 'lucide-bottle-wine'],
            ['name' => 'Bier', 'icon' => 'lucide-beer'],
            ['name' => 'Kaffee', 'icon' => 'lucide-coffee'],
            ['name' => 'Snacks', 'icon' => 'lucide-cookie'],
            ['name' => 'Sonstiges', 'icon' => 'lucide-shapes'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate([
                'name' => $data['name'],
            ], [
                'icon' => $data['icon'],
            ]);
        }
    }
}
