<?php

namespace App\Repositories\Contracts;

interface TableMergeRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByGroupCode(string $groupCode);
    public function findByOrder(int $orderId);
    public function create(array $data);
    public function addTablesToMerge(int $mergeId, array $tableIds);
    public function getTablesForMerge(int $mergeId);
}
