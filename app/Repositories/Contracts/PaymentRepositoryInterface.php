<?php

namespace App\Repositories\Contracts;

interface PaymentRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByOrder(int $orderId);
    public function today();
    public function betweenDates(string $from, string $to);
    public function create(array $data);
    public function totalForOrder(int $orderId);
}
