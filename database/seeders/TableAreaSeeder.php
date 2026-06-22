<?php

namespace Database\Seeders;

use App\Models\TableArea;
use Illuminate\Database\Seeder;

class TableAreaSeeder extends Seeder
{
    public function run(): void
    {
        TableArea::firstOrCreate(['name' => 'Ground Floor'], ['sort_order' => 1]);
        TableArea::firstOrCreate(['name' => 'First Floor'], ['sort_order' => 2]);
        TableArea::firstOrCreate(['name' => 'VIP Room'], ['sort_order' => 3]);
        TableArea::firstOrCreate(['name' => 'Outdoor'], ['sort_order' => 4]);
        TableArea::firstOrCreate(['name' => 'Terrace'], ['sort_order' => 5]);
    }
}
