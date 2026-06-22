<?php

namespace App\Repositories\Contracts;

interface TableRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByTableNo(string $tableNo);
    public function findByArea(int $areaId);
    public function findByStatus(string $status);
    public function available();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function updateStatus(int $id, string $status);
    public function paginate(int $perPage = 25);
}
