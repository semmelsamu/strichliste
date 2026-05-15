<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Apfelschorle naturtrüb 0,5l', 'price' => 1, 'category' => 'Wasser und Säfte'],
            ['name' => 'Balisto', 'price' => 1.20, 'category' => 'Snacks'],
            ['name' => 'Eichhofner Helles 0,5l', 'price' => 1.50, 'category' => 'Bier'],
            ['name' => 'Eichhofner 333 0,5l', 'price' => 1.50, 'category' => 'Bier'],
            ['name' => 'Gösser Radler 0,5l', 'price' => 1.50, 'category' => 'Bier'],
            ['name' => 'Club Mate 0,5l', 'price' => 1.20, 'category' => 'Softdrinks'],
            ['name' => 'Kaffee', 'price' => 0.60, 'category' => 'Kaffee'],
            ['name' => 'Duplo', 'price' => 0.30, 'category' => 'Snacks'],
            ['name' => 'KitKat', 'price' => 0.80, 'category' => 'Snacks'],
            ['name' => 'Mars', 'price' => 0.80, 'category' => 'Snacks'],
            ['name' => 'Mio Mio Mate Banana 0,5l', 'price' => 1.50, 'category' => 'Softdrinks'],
            ['name' => 'Mio Mio Mate 0,5l', 'price' => 1.50, 'category' => 'Softdrinks'],
            ['name' => 'Mio Mio Mate Zero 0,5l', 'price' => 1.50, 'category' => 'Softdrinks'],
            ['name' => 'Mio Mio Cola 0,5l', 'price' => 1.50, 'category' => 'Softdrinks'],
            ['name' => 'Paulaner Spezi 0,5l', 'price' => 1.00, 'category' => 'Softdrinks'],
            ['name' => 'ültje Studentenfutter', 'price' => 1.20, 'category' => 'Snacks'],
            ['name' => 'Labertaler Medium', 'price' => 0.60, 'category' => 'Wasser und Säfte'],
        ];

        foreach ($products as $data) {
            $category = Category::where('name', $data['category'])->firstOrFail();

            Product::firstOrCreate(
                ['name' => $data['name']],
                ['price' => $data['price'], 'category_id' => $category->id],
            );
        }
    }
}
