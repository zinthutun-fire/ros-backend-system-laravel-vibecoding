<?php

namespace App\Console\Commands;

use App\Services\TableService;
use Illuminate\Console\Command;

class TablesReset extends Command
{
    protected $signature = 'tables:reset';
    protected $description = 'Delete all orders and reset all tables to available';

    public function handle(TableService $tableService): int
    {
        if (!$this->confirm('This will delete ALL order data and reset all tables. Are you sure?')) {
            return self::FAILURE;
        }

        $tableService->resetAll();

        $this->info('All orders deleted and tables reset to available.');
        return self::SUCCESS;
    }
}
