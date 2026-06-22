<?php

namespace App\Repositories\Contracts;

interface MenuItemModifierRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function findByMenuItem(int $menuItemId);
    public function active();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
