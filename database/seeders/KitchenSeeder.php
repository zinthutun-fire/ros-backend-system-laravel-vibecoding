<?php

namespace Database\Seeders;

use App\Models\Kitchen;
use Illuminate\Database\Seeder;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        Kitchen::firstOrCreate(['code' => 'MK'], ['name' => 'Main Kitchen', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'DB'], ['name' => 'Drink Bar', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'DS'], ['name' => 'Dessert', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'GS'], ['name' => 'Grill Station', 'status' => 'active']);
        Kitchen::firstOrCreate(['code' => 'SB'], ['name' => 'Salad Bar', 'status' => 'active']);
    }
}
