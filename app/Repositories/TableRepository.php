<?php

namespace App\Repositories;

use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;

class TableRepository implements TableRepositoryInterface
{
    public function all()
    {
        return Table::with('area')->orderBy('sort_order')->orderBy('table_no')->get();
    }

    public function find(int $id)
    {
        return Table::with('area')->findOrFail($id);
    }

    public function findByTableNo(string $tableNo)
    {
        return Table::with('area')->where('table_no', $tableNo)->first();
    }

    public function findByArea(int $areaId)
    {
        return Table::with('area')->where('area_id', $areaId)->orderBy('table_no')->get();
    }

    public function findByStatus(string $status)
    {
        return Table::with('area')->where('status', $status)->orderBy('table_no')->get();
    }

    public function available()
    {
        return Table::with('area')->where('status', 'available')->orderBy('table_no')->get();
    }

    public function create(array $data)
    {
        return Table::create($data);
    }

    public function update(int $id, array $data)
    {
        $table = $this->find($id);
        $table->update($data);
        return $table;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }

    public function updateStatus(int $id, string $status)
    {
        $table = $this->find($id);
        $table->update(['status' => $status]);
        return $table;
    }

    public function paginate(int $perPage = 100)
    {
        return Table::with('area')->orderBy('sort_order')->paginate($perPage);
    }
}
