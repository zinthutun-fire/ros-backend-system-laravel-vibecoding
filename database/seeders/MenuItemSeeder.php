<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // Appetizers (category 1) - Main Kitchen (kitchen 1)
        $springRolls = MenuItem::firstOrCreate(['name' => 'Spring Rolls'], ['category_id' => 1, 'kitchen_id' => 1, 'price' => 6.50, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Chicken Wings'], ['category_id' => 1, 'kitchen_id' => 1, 'price' => 8.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Nachos Supreme'], ['category_id' => 1, 'kitchen_id' => 1, 'price' => 9.50, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Garlic Bread'], ['category_id' => 1, 'kitchen_id' => 1, 'price' => 4.50, 'sort_order' => 4, 'status' => 'available']);

        // Main Course (category 2) - Main Kitchen (kitchen 1)
        $steak = MenuItem::firstOrCreate(['name' => 'Grilled Steak'], ['category_id' => 2, 'kitchen_id' => 4, 'price' => 22.00, 'has_modifiers' => true, 'sort_order' => 1, 'status' => 'available']);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Medium Rare'], ['price_adjustment' => 0, 'sort_order' => 1]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Medium'], ['price_adjustment' => 0, 'sort_order' => 2]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Well Done'], ['price_adjustment' => 0, 'sort_order' => 3]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Extra Sauce'], ['price_adjustment' => 1.50, 'sort_order' => 4]);

        $pasta = MenuItem::firstOrCreate(['name' => 'Pasta Carbonara'], ['category_id' => 2, 'kitchen_id' => 1, 'price' => 14.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Grilled Salmon'], ['category_id' => 2, 'kitchen_id' => 1, 'price' => 18.50, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Chicken Parmesan'], ['category_id' => 2, 'kitchen_id' => 1, 'price' => 15.00, 'sort_order' => 4, 'status' => 'available']);

        // Burgers (category 3) - Main Kitchen (kitchen 1)
        $classicBurger = MenuItem::firstOrCreate(['name' => 'Classic Burger'], ['category_id' => 3, 'kitchen_id' => 4, 'price' => 10.50, 'has_modifiers' => true, 'sort_order' => 1, 'status' => 'available']);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $classicBurger->id, 'name' => 'Extra Cheese'], ['price_adjustment' => 1.50, 'sort_order' => 1]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $classicBurger->id, 'name' => 'Bacon'], ['price_adjustment' => 2.00, 'sort_order' => 2]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $classicBurger->id, 'name' => 'No Onions'], ['price_adjustment' => 0, 'sort_order' => 3]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $classicBurger->id, 'name' => 'Extra Patty'], ['price_adjustment' => 3.00, 'sort_order' => 4]);

        MenuItem::firstOrCreate(['name' => 'Cheese Burger'], ['category_id' => 3, 'kitchen_id' => 4, 'price' => 11.50, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'BBQ Bacon Burger'], ['category_id' => 3, 'kitchen_id' => 4, 'price' => 13.00, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Veggie Burger'], ['category_id' => 3, 'kitchen_id' => 4, 'price' => 9.50, 'sort_order' => 4, 'status' => 'available']);

        // Pizza (category 4) - Main Kitchen (kitchen 1)
        MenuItem::firstOrCreate(['name' => 'Margherita Pizza'], ['category_id' => 4, 'kitchen_id' => 1, 'price' => 11.00, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Pepperoni Pizza'], ['category_id' => 4, 'kitchen_id' => 1, 'price' => 13.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Hawaiian Pizza'], ['category_id' => 4, 'kitchen_id' => 1, 'price' => 14.00, 'sort_order' => 3, 'status' => 'available']);

        // Salads (category 5) - Salad Bar (kitchen 5)
        MenuItem::firstOrCreate(['name' => 'Caesar Salad'], ['category_id' => 5, 'kitchen_id' => 5, 'price' => 8.50, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Greek Salad'], ['category_id' => 5, 'kitchen_id' => 5, 'price' => 9.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Garden Salad'], ['category_id' => 5, 'kitchen_id' => 5, 'price' => 7.00, 'sort_order' => 3, 'status' => 'available']);

        // Beverages (category 6) - Drink Bar (kitchen 2)
        MenuItem::firstOrCreate(['name' => 'Coca Cola'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 2.00, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Orange Juice'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 3.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Mineral Water'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 1.50, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Iced Tea'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 2.50, 'sort_order' => 4, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Coffee'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 2.50, 'sort_order' => 5, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Beer'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 5.00, 'sort_order' => 6, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Wine (Glass)'], ['category_id' => 6, 'kitchen_id' => 2, 'price' => 6.00, 'sort_order' => 7, 'status' => 'available']);

        // Desserts (category 7) - Dessert (kitchen 3)
        MenuItem::firstOrCreate(['name' => 'Ice Cream'], ['category_id' => 7, 'kitchen_id' => 3, 'price' => 3.50, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Chocolate Cake'], ['category_id' => 7, 'kitchen_id' => 3, 'price' => 5.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Cheesecake'], ['category_id' => 7, 'kitchen_id' => 3, 'price' => 5.50, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Tiramisu'], ['category_id' => 7, 'kitchen_id' => 3, 'price' => 6.00, 'sort_order' => 4, 'status' => 'available']);

        // Sides (category 8) - Main Kitchen (kitchen 1)
        MenuItem::firstOrCreate(['name' => 'French Fries'], ['category_id' => 8, 'kitchen_id' => 1, 'price' => 3.50, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Onion Rings'], ['category_id' => 8, 'kitchen_id' => 1, 'price' => 4.00, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'Coleslaw'], ['category_id' => 8, 'kitchen_id' => 1, 'price' => 2.50, 'sort_order' => 3, 'status' => 'available']);
    }
}
