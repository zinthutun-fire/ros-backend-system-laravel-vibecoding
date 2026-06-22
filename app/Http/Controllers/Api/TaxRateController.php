<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Http\Resources\TaxRateResource;
use App\Repositories\Contracts\TaxRateRepositoryInterface;
use Illuminate\Http\JsonResponse;

class TaxRateController extends Controller
{
    public function __construct(
        protected TaxRateRepositoryInterface $taxRateRepository
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(TaxRateResource::collection($this->taxRateRepository->all()));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new TaxRateResource($this->taxRateRepository->find($id)));
    }

    public function store(StoreTaxRateRequest $request): JsonResponse
    {
        return response()->json(new TaxRateResource($this->taxRateRepository->create($request->validated())), 201);
    }

    public function update(int $id, UpdateTaxRateRequest $request): JsonResponse
    {
        return response()->json(new TaxRateResource($this->taxRateRepository->update($id, $request->validated())));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->taxRateRepository->delete($id);
        return response()->json(['message' => 'Tax rate deleted successfully']);
    }
}
