<?php

namespace App\Repositories;

use App\Models\MenuItemModifier;
use App\Repositories\Contracts\MenuItemModifierRepositoryInterface;

class MenuItemModifierRepository implements MenuItemModifierRepositoryInterface
{
    public function all()
    {
        return MenuItemModifier::orderBy('sort_order')->get();
    }

    public function find(int $id)
    {
        return MenuItemModifier::findOrFail($id);
    }

    public function findByMenuItem(int $menuItemId)
    {
        return MenuItemModifier::where('menu_item_id', $menuItemId)
            ->orderBy('sort_order')
            ->get();
    }

    public function active()
    {
        return MenuItemModifier::active()->orderBy('sort_order')->get();
    }

    public function create(array $data)
    {
        return MenuItemModifier::create($data);
    }

    public function update(int $id, array $data)
    {
        $modifier = $this->find($id);
        $modifier->update($data);
        return $modifier;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
