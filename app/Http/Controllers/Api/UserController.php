<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userRepository->paginate(request()->get('per_page', 25));
        return response()->json(UserResource::collection($users)->response()->getData(true));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new UserResource($this->userRepository->find($id)));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);
        return response()->json(new UserResource($user), 201);
    }

    public function update(int $id, UpdateUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user = $this->userRepository->update($id, $data);
        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userRepository->delete($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
