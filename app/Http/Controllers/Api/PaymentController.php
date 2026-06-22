<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository
    ) {}

    public function index(): JsonResponse
    {
        $orderId = request()->get('order_id');
        if ($orderId) {
            $payments = $this->paymentRepository->findByOrder($orderId);
        } else {
            $payments = $this->paymentRepository->all();
        }
        return response()->json(PaymentResource::collection($payments));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(new PaymentResource($this->paymentRepository->find($id)));
    }
}
