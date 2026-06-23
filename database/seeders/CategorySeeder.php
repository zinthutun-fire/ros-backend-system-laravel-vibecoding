<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'ထမင်းများ (Rice Dishes)'], ['description' => 'Rice dishes and fried rice', 'sort_order' => 1, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'ခေါက်ဆွဲများ (Noodles)'], ['description' => 'Noodle dishes', 'sort_order' => 2, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'အကြော်များ (Fried Items)'], ['description' => 'Fried food selection', 'sort_order' => 3, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'အသားများ (Meat Dishes)'], ['description' => 'Meat dishes', 'sort_order' => 4, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'အသုပ်များ (Salads)'], ['description' => 'Fresh salads', 'sort_order' => 5, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'ဟင်းချိုများ (Soups)'], ['description' => 'Soup selection', 'sort_order' => 6, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'အချိုရည်များ (Beverages)'], ['description' => 'Drinks and beverages', 'sort_order' => 7, 'is_active' => true]);
        Category::firstOrCreate(['name' => 'အချိုပွဲများ (Desserts)'], ['description' => 'Sweet treats', 'sort_order' => 8, 'is_active' => true]);
    }
}
