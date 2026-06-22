<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByOrderNo(string $orderNo);
    public function findByTable(int $tableId);
    public function findByStatus(string $status);
    public function active();
    public function today();
    public function betweenDates(string $from, string $to);
    public function create(array $data);
    public function update(int $id, array $data);
    public function updateStatus(int $id, string $status);
    public function paginate(int $perPage = 25);
    public function generateOrderNo(): string;
}
