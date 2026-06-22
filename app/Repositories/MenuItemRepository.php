<?php

namespace App\Repositories;

use App\Models\MenuItem;
use App\Repositories\Contracts\MenuItemRepositoryInterface;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function all()
    {
        return MenuItem::with(['category', 'kitchen', 'activeModifiers'])
            ->ordered()
            ->get();
    }

    public function find(int $id)
    {
        return MenuItem::with(['category', 'kitchen', 'activeModifiers'])->findOrFail($id);
    }

    public function findByCategory(int $categoryId)
    {
        return MenuItem::with(['category', 'kitchen', 'activeModifiers'])
            ->byCategory($categoryId)
            ->ordered()
            ->get();
    }

    public function findByKitchen(int $kitchenId)
    {
        return MenuItem::with(['category', 'kitchen', 'activeModifiers'])
            ->byKitchen($kitchenId)
            ->ordered()
            ->get();
    }

    public function available()
    {
        return MenuItem::with(['category', 'kitchen', 'activeModifiers'])
            ->available()
            ->ordered()
            ->get();
    }

    public function create(array $data)
    {
        return MenuItem::create($data);
    }

    public function update(int $id, array $data)
    {
        $item = $this->find($id);
        $item->update($data);
        return $item->load(['category', 'kitchen', 'activeModifiers']);
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }

    public function paginate(int $perPage = 25)
    {
        return MenuItem::with(['category', 'kitchen', 'activeModifiers'])
            ->ordered()
            ->paginate($perPage);
    }
}
