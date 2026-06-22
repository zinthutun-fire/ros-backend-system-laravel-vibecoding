<?php

namespace App\Repositories;

use App\Models\TableArea;
use App\Repositories\Contracts\TableAreaRepositoryInterface;

class TableAreaRepository implements TableAreaRepositoryInterface
{
    public function all()
    {
        return TableArea::orderBy('sort_order')->get();
    }

    public function find(int $id)
    {
        return TableArea::findOrFail($id);
    }

    public function create(array $data)
    {
        return TableArea::create($data);
    }

    public function update(int $id, array $data)
    {
        $area = $this->find($id);
        $area->update($data);
        return $area;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
