<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function all()
    {
        return User::with('kitchen')->orderBy('name')->get();
    }

    public function find(int $id)
    {
        return User::with('kitchen')->findOrFail($id);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function findByRole(string $role)
    {
        return User::where('role', $role)->orderBy('name')->get();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = $this->find($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }

    public function paginate(int $perPage = 25)
    {
        return User::with('kitchen')->orderBy('name')->paginate($perPage);
    }
}
