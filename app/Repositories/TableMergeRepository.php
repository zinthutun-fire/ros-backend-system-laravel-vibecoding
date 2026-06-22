<?php

namespace App\Repositories;

use App\Models\TableMerge;
use App\Models\MergeGroupTable;
use App\Repositories\Contracts\TableMergeRepositoryInterface;

class TableMergeRepository implements TableMergeRepositoryInterface
{
    public function all()
    {
        return TableMerge::with(['order', 'tables', 'createdBy'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function find(int $id)
    {
        return TableMerge::with(['order', 'tables', 'createdBy'])->findOrFail($id);
    }

    public function findByGroupCode(string $groupCode)
    {
        return TableMerge::with(['order', 'tables', 'createdBy'])
            ->where('group_code', $groupCode)
            ->firstOrFail();
    }

    public function findByOrder(int $orderId)
    {
        return TableMerge::with(['order', 'tables', 'createdBy'])
            ->where('order_id', $orderId)
            ->first();
    }

    public function create(array $data)
    {
        return TableMerge::create($data);
    }

    public function addTablesToMerge(int $mergeId, array $tableIds)
    {
        foreach ($tableIds as $tableId) {
            MergeGroupTable::firstOrCreate([
                'table_merge_id' => $mergeId,
                'table_id' => $tableId,
            ]);
        }
        return $this->find($mergeId);
    }

    public function getTablesForMerge(int $mergeId)
    {
        return MergeGroupTable::where('table_merge_id', $mergeId)
            ->with('table')
            ->get()
            ->pluck('table');
    }
}
