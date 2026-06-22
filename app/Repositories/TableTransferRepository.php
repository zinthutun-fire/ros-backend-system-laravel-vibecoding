<?php

namespace App\Repositories;

use App\Models\TableTransfer;
use App\Repositories\Contracts\TableTransferRepositoryInterface;

class TableTransferRepository implements TableTransferRepositoryInterface
{
    public function all()
    {
        return TableTransfer::with(['fromTable', 'toTable', 'order', 'user'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function find(int $id)
    {
        return TableTransfer::with(['fromTable', 'toTable', 'order', 'user'])->findOrFail($id);
    }

    public function findByOrder(int $orderId)
    {
        return TableTransfer::where('order_id', $orderId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findByFromTable(int $tableId)
    {
        return TableTransfer::where('from_table_id', $tableId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findByToTable(int $tableId)
    {
        return TableTransfer::where('to_table_id', $tableId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data)
    {
        return TableTransfer::create($data);
    }
}
