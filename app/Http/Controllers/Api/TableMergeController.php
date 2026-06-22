<?php

namespace App\Http\Controllers\Api;

use App\Events\TableMerged;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableMergeRequest;
use App\Services\TableMergeService;
use Illuminate\Http\JsonResponse;

class TableMergeController extends Controller
{
    public function __construct(
        protected TableMergeService $tableMergeService
    ) {}

    public function store(StoreTableMergeRequest $request): JsonResponse
    {
        $result = $this->tableMergeService->mergeTables(
            $request->validated()['table_ids'],
            $request->user()->id
        );

        try {
            event(new TableMerged($result['merge']));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed during merge: ' . $e->getMessage());
        }

        return response()->json($result, 201);
    }
}
