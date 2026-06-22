<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::statement('SET FOREIGN_KEY_CHECKS=0');

$tables = ['merge_group_tables', 'order_item_modifiers', 'order_items', 'payments', 'table_merges', 'table_transfers', 'orders'];
foreach ($tables as $table) {
    $count = DB::table($table)->count();
    DB::table($table)->truncate();
    echo "Truncated $table ($count rows)\n";
}

$updated = DB::table('tables')->where('status', '!=', 'available')->update(['status' => 'available']);
echo "Reset $updated table statuses to available\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1');
echo "Done\n";
