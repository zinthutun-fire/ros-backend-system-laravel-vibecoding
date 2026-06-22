<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Services\PaymentService;
use App\Services\OrderService;
use App\Http\Requests\StorePaymentRequest;
use App\Events\PaymentCompleted;
use Illuminate\Http\JsonResponse;

class CashierController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected TableRepositoryInterface $tableRepository,
        protected PaymentService $paymentService,
        protected OrderService $orderService,
    ) {}

    public function activeOrders(): JsonResponse
    {
        $status = request()->get('status', 'completed');
        $orders = $this->orderRepository->findByStatus($status);
        return response()->json(OrderResource::collection($orders));
    }

    public function processPayment(StorePaymentRequest $request): JsonResponse
    {
        $result = $this->paymentService->processPayment($request->validated());

        $payment = $result['payment'];
        $payment->load('order.table');

        event(new PaymentCompleted($payment));

        return response()->json($result);
    }
}
