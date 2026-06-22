<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            1 => ['Ground Floor', 20],
            2 => ['First Floor', 15],
            3 => ['VIP Room', 5],
            4 => ['Outdoor', 10],
            5 => ['Terrace', 8],
        ];

        $tableNo = 1;
        foreach ($areas as $areaId => [$name, $count]) {
            for ($i = 1; $i <= $count; $i++) {
                $tno = 'T' . str_pad($tableNo, 2, '0', STR_PAD_LEFT);
                Table::firstOrCreate(['table_no' => $tno], [
                    'name' => $name . ' - Table ' . $i,
                    'capacity' => $i % 5 === 0 ? 8 : ($i % 3 === 0 ? 6 : 4),
                    'area_id' => $areaId,
                    'status' => 'available',
                    'sort_order' => $tableNo,
                ]);
                $tableNo++;
            }
        }
    }
}
