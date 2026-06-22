<?php

namespace App\Repositories;

use App\Models\Kitchen;
use App\Repositories\Contracts\KitchenRepositoryInterface;

class KitchenRepository implements KitchenRepositoryInterface
{
    public function all()
    {
        return Kitchen::orderBy('name')->get();
    }

    public function find(int $id)
    {
        return Kitchen::findOrFail($id);
    }

    public function findByCode(string $code)
    {
        return Kitchen::where('code', $code)->first();
    }

    public function active()
    {
        return Kitchen::where('status', 'active')->orderBy('name')->get();
    }

    public function create(array $data)
    {
        return Kitchen::create($data);
    }

    public function update(int $id, array $data)
    {
        $kitchen = $this->find($id);
        $kitchen->update($data);
        return $kitchen;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
