<?php

namespace App\Repositories\Contracts;

interface OrderItemRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByOrder(int $orderId);
    public function findByKitchen(int $kitchenId);
    public function findByStatus(string $status);
    public function create(array $data);
    public function createMany(array $items);
    public function update(int $id, array $data);
    public function updateStatus(int $id, string $status);
    public function bulkUpdateStatus(array $ids, string $status);
    public function delete(int $id);
    public function void(int $id, int $userId, string $reason);
}
