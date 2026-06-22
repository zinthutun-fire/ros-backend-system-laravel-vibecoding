<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKitchenRequest;
use App\Http\Requests\UpdateKitchenRequest;
use App\Http\Resources\KitchenResource;
use App\Repositories\Contracts\KitchenRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function __construct(
        protected KitchenRepositoryInterface $kitchenRepository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(KitchenResource::collection($this->kitchenRepository->all()));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new KitchenResource($this->kitchenRepository->find($id)));
    }

    public function store(StoreKitchenRequest $request): JsonResponse
    {
        return response()->json(new KitchenResource($this->kitchenRepository->create($request->validated())), 201);
    }

    public function update(int $id, UpdateKitchenRequest $request): JsonResponse
    {
        return response()->json(new KitchenResource($this->kitchenRepository->update($id, $request->validated())));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->kitchenRepository->delete($id);
        return response()->json(['message' => 'Kitchen deleted successfully']);
    }
}
