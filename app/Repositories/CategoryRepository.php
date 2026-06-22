<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all()
    {
        return Category::ordered()->get();
    }

    public function find(int $id)
    {
        return Category::findOrFail($id);
    }

    public function active()
    {
        return Category::active()->ordered()->get();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(int $id, array $data)
    {
        $category = $this->find($id);
        $category->update($data);
        return $category;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
