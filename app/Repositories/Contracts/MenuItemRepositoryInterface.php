<?php

namespace App\Repositories\Contracts;

interface MenuItemRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByCategory(int $categoryId);
    public function findByKitchen(int $kitchenId);
    public function available();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function paginate(int $perPage = 25);
}
