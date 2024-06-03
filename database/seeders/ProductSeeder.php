<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed ingredients
        $beef = Ingredient::create(['name' => 'Beef', 'stock' => 20000, 'available' => 20000]); // 20kg
        $cheese = Ingredient::create(['name' => 'Cheese', 'stock' => 5000, 'available' => 5000]); // 5kg
        $onion = Ingredient::create(['name' => 'Onion', 'stock' => 1000, 'available' => 1000]); // 1kg

        // Seed product
        $burger = Product::create(['name' => 'Burger']);

        // Attach ingredients to the product
        $burger->ingredients()->attach([
            $beef->id => ['amount' => 150],
            $cheese->id => ['amount' => 30],
            $onion->id => ['amount' => 20],
        ]);
    }
}
