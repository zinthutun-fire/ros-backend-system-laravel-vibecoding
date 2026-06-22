<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function active();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
