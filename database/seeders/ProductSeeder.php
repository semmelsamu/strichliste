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
            ['name' => 'Paulaner Spezi', 'price' => 1.20, 'category' => 'Softdrinks'],
            ['name' => 'Augustiner Hell', 'price' => 1.50, 'category' => 'Bier'],
            ['name' => 'Club Mate', 'price' => 1.20, 'category' => 'Softdrinks'],
            ['name' => 'Kaffee Crema', 'price' => 0.60, 'category' => 'Kaffee'],
            ['name' => 'Snickers', 'price' => 0.80, 'category' => 'Snacks'],
            ['name' => 'Apfelschorle', 'price' => 1.20, 'category' => 'Wasser und Säfte'],
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
