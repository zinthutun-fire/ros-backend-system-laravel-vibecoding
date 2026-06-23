<?php

namespace Database\Seeders;

use App\Models\Kitchen;
use Illuminate\Database\Seeder;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        Kitchen::firstOrCreate(['code' => 'MK'], ['name' => 'မီးဖိုချောင် (Main Kitchen)', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'DB'], ['name' => 'အချိုရည်ဘား (Drink Bar)', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'DS'], ['name' => 'အချိုပွဲ (Dessert)', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'GR'], ['name' => 'ကင်ဆိုင် (Grill Station)', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'SD'], ['name' => 'သုပ်ခန်း (Salad Station)', 'status' => 'active']);
    }
}
