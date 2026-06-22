<?php

namespace App\Repositories;

use App\Models\TaxRate;
use App\Repositories\Contracts\TaxRateRepositoryInterface;

class TaxRateRepository implements TaxRateRepositoryInterface
{
    public function all()
    {
        return TaxRate::orderBy('name')->get();
    }

    public function find(int $id)
    {
        return TaxRate::findOrFail($id);
    }

    public function active()
    {
        return TaxRate::active()->orderBy('name')->get();
    }

    public function default()
    {
        return TaxRate::default()->first();
    }

    public function byType(string $type)
    {
        return TaxRate::where('type', $type)->active()->get();
    }

    public function create(array $data)
    {
        return TaxRate::create($data);
    }

    public function update(int $id, array $data)
    {
        $rate = $this->find($id);
        $rate->update($data);
        return $rate;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
