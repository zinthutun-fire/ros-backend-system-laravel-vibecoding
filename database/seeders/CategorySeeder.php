<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'Appetizers'], ['description' => 'Starters and appetizers', 'sort_order' => 1, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Main Course'], ['description' => 'Main dishes', 'sort_order' => 2, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Burgers'], ['description' => 'Burger selection', 'sort_order' => 3, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Pizza'], ['description' => 'Wood-fired pizzas', 'sort_order' => 4, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Salads'], ['description' => 'Fresh salads', 'sort_order' => 5, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Beverages'], ['description' => 'Drinks and beverages', 'sort_order' => 6, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Desserts'], ['description' => 'Sweet treats', 'sort_order' => 7, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Sides'], ['description' => 'Side dishes', 'sort_order' => 8, 'is_active' => true]);
    }
}
