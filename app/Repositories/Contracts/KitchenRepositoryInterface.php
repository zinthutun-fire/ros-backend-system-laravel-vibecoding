<?php

namespace App\Repositories\Contracts;

interface KitchenRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByCode(string $code);
    public function active();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
