<?php

namespace App\Repositories\Contracts;

interface TableTransferRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByOrder(int $orderId);
    public function findByFromTable(int $tableId);
    public function findByToTable(int $tableId);
    public function create(array $data);
}
