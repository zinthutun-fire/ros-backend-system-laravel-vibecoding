<?php

namespace App\Http\Controllers\Api;

use App\Events\TableTransferred;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableTransferRequest;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;

class TableTransferController extends Controller
{
    public function __construct(
        protected TableService $tableService
    ) {}

    public function store(StoreTableTransferRequest $request): JsonResponse
    {
        $result = $this->tableService->transferTable(
            $request->validated()['order_id'],
            $request->validated()['from_table_id'],
            $request->validated()['to_table_id'],
            $request->user()->id
        );

        event(new TableTransferred($result['transfer']));

        return response()->json($result);
    }
}
