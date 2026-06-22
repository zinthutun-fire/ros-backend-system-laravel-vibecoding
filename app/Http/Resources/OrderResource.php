<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_no' => $this->order_no,
            'table' => new TableResource($this->whenLoaded('table')),
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'total' => (float) $this->total,
            'tax_total' => (float) $this->tax_total,
            'service_charge_total' => (float) $this->service_charge_total,
            'discount_total' => (float) $this->discount_total,
            'grand_total' => (float) $this->grand_total,
            'notes' => $this->notes,
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'paid_by' => new UserResource($this->whenLoaded('paidBy')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'merge' => $this->whenLoaded('merge', fn() => [
                'group_code' => $this->merge->group_code,
                'tables' => $this->merge->tables->pluck('table_no'),
            ]),
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
