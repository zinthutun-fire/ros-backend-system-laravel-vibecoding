<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Kitchen;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $kitchen = fn($code) => Kitchen::where('code', $code)->first()->id;
        $cat = fn($name) => Category::where('name', 'LIKE', "%$name%")->first()->id;

        // === ထမင်းများ (Rice Dishes) - Main Kitchen ===
        $riceCat = $cat('Rice');
        $mk = $kitchen('MK');

        MenuItem::firstOrCreate(['name' => 'ထမင်းကြော် (Fried Rice)'], ['category_id' => $riceCat, 'kitchen_id' => $mk, 'price' => 5000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ကြက်သားထမင်း (Chicken Rice)'], ['category_id' => $riceCat, 'kitchen_id' => $mk, 'price' => 6000, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ဝက်သားထမင်း (Pork Rice)'], ['category_id' => $riceCat, 'kitchen_id' => $mk, 'price' => 5500, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ပုဇွန်ထမင်းကြော် (Shrimp Fried Rice)'], ['category_id' => $riceCat, 'kitchen_id' => $mk, 'price' => 7000, 'sort_order' => 4, 'status' => 'available']);

        // === ခေါက်ဆွဲများ (Noodles) - Main Kitchen ===
        $noodleCat = $cat('Noodles');

        MenuItem::firstOrCreate(['name' => 'မုန့်ဟင်းခါး (Mohinga)'], ['category_id' => $noodleCat, 'kitchen_id' => $mk, 'price' => 3000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ကြက်ဆူကြော် (Fried Noodles)'], ['category_id' => $noodleCat, 'kitchen_id' => $mk, 'price' => 4500, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ရွှေကြာဆံ (Shwe Kyah)'], ['category_id' => $noodleCat, 'kitchen_id' => $mk, 'price' => 4000, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ခေါက်ဆွဲကြော် (Pad Thai)'], ['category_id' => $noodleCat, 'kitchen_id' => $mk, 'price' => 5000, 'sort_order' => 4, 'status' => 'available']);

        // === အကြော်များ (Fried Items) - Main Kitchen ===
        $friedCat = $cat('Fried');

        MenuItem::firstOrCreate(['name' => 'ကြက်ကြော် (Fried Chicken)'], ['category_id' => $friedCat, 'kitchen_id' => $mk, 'price' => 7000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'အာလူးကြော် (French Fries)'], ['category_id' => $friedCat, 'kitchen_id' => $mk, 'price' => 3000, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ကြက်သားကြော် (Spring Rolls)'], ['category_id' => $friedCat, 'kitchen_id' => $mk, 'price' => 4000, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ပုဇွန်ကြော် (Fried Shrimp)'], ['category_id' => $friedCat, 'kitchen_id' => $mk, 'price' => 8000, 'sort_order' => 4, 'status' => 'available']);

        // === အသားများ (Meat Dishes) - Grill Station ===
        $meatCat = $cat('Meat');
        $gr = $kitchen('GR');

        $steak = MenuItem::firstOrCreate(['name' => 'အမဲသားကင် (Grilled Steak)'], ['category_id' => $meatCat, 'kitchen_id' => $gr, 'price' => 15000, 'has_modifiers' => true, 'sort_order' => 1, 'status' => 'available']);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Medium Rare'], ['price_adjustment' => 0, 'sort_order' => 1]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Medium'], ['price_adjustment' => 0, 'sort_order' => 2]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'Well Done'], ['price_adjustment' => 0, 'sort_order' => 3]);
        MenuItemModifier::firstOrCreate(['menu_item_id' => $steak->id, 'name' => 'ဆော့စ်ထပ် (Extra Sauce)'], ['price_adjustment' => 1000, 'sort_order' => 4]);

        MenuItem::firstOrCreate(['name' => 'ကြက်သားကင် (Grilled Chicken)'], ['category_id' => $meatCat, 'kitchen_id' => $gr, 'price' => 8000, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ဝက်သားကင် (Grilled Pork)'], ['category_id' => $meatCat, 'kitchen_id' => $gr, 'price' => 9000, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ငါးကင် (Grilled Fish)'], ['category_id' => $meatCat, 'kitchen_id' => $gr, 'price' => 10000, 'sort_order' => 4, 'status' => 'available']);

        // === အသုပ်များ (Salads) - Salad Station ===
        $saladCat = $cat('Salads');
        $sd = $kitchen('SD');

        MenuItem::firstOrCreate(['name' => 'ဂျုံသုပ် (Thousand Layer Salad)'], ['category_id' => $saladCat, 'kitchen_id' => $sd, 'price' => 3000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ခရမ်းချဉ်သုပ် (Tomato Salad)'], ['category_id' => $saladCat, 'kitchen_id' => $sd, 'price' => 2500, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ငါးပိသုပ် (Fish Paste Salad)'], ['category_id' => $saladCat, 'kitchen_id' => $sd, 'price' => 2000, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'သီးစုံသုပ် (Mixed Salad)'], ['category_id' => $saladCat, 'kitchen_id' => $sd, 'price' => 3500, 'sort_order' => 4, 'status' => 'available']);

        // === ဟင်းချိုများ (Soups) - Main Kitchen ===
        $soupCat = $cat('Soups');

        MenuItem::firstOrCreate(['name' => 'ကြက်သားပြုတ်ရည် (Chicken Soup)'], ['category_id' => $soupCat, 'kitchen_id' => $mk, 'price' => 3000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ငါးရဲချဉ်စိမ်း (Fish Sour Soup)'], ['category_id' => $soupCat, 'kitchen_id' => $mk, 'price' => 3500, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ခရမ်းချဉ်ဟင်းချို (Tomato Soup)'], ['category_id' => $soupCat, 'kitchen_id' => $mk, 'price' => 2500, 'sort_order' => 3, 'status' => 'available']);

        // === အချိုရည်များ (Beverages) - Drink Bar ===
        $bevCat = $cat('Beverages');
        $db = $kitchen('DB');

        MenuItem::firstOrCreate(['name' => 'ကိုကာကိုလာ (Coca Cola)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 2000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'လိမ္မော်ရည် (Orange Juice)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 2500, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ရေသန့် (Mineral Water)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 1000, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ရေခဲလက်ဖက်ရည် (Iced Tea)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 2000, 'sort_order' => 4, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ကော်ဖီ (Coffee)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 2500, 'sort_order' => 5, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'မြန်မာလက်ဖက်ရည် (Myanmar Tea)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 1500, 'sort_order' => 6, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ဘီယာ (Beer)'], ['category_id' => $bevCat, 'kitchen_id' => $db, 'price' => 4000, 'sort_order' => 7, 'status' => 'available']);

        // === အချိုပွဲများ (Desserts) - Dessert ===
        $dessCat = $cat('Desserts');
        $ds = $kitchen('DS');

        MenuItem::firstOrCreate(['name' => 'ရေခဲမုန့် (Ice Cream)'], ['category_id' => $dessCat, 'kitchen_id' => $ds, 'price' => 3000, 'sort_order' => 1, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ချောကလက်ကိတ် (Chocolate Cake)'], ['category_id' => $dessCat, 'kitchen_id' => $ds, 'price' => 5000, 'sort_order' => 2, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'ပူတင်း (Pudding)'], ['category_id' => $dessCat, 'kitchen_id' => $ds, 'price' => 3500, 'sort_order' => 3, 'status' => 'available']);
        MenuItem::firstOrCreate(['name' => 'မုန့်လက်ဆောင်း (Mont Lin Ma Yar)'], ['category_id' => $dessCat, 'kitchen_id' => $ds, 'price' => 2500, 'sort_order' => 4, 'status' => 'available']);
    }
}
