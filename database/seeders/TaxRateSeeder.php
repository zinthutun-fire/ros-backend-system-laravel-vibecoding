<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        TaxRate::firstOrCreate(
            ['name' => 'VAT', 'type' => 'tax'],
            ['rate' => 10.00, 'is_default' => true, 'is_active' => true]
        );

        TaxRate::firstOrCreate(
            ['name' => 'Service Charge', 'type' => 'service_charge'],
            ['rate' => 5.00, 'is_default' => true, 'is_active' => true]
        );
    }
}
