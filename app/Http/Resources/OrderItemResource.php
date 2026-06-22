<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'menu_item_id' => $this->menu_item_id,
            'name' => $this->menuItem?->name,
            'kitchen_id' => $this->kitchen_id,
            'kitchen' => $this->kitchen?->name,
            'qty' => $this->qty,
            'price' => (float) $this->price,
            'subtotal' => (float) $this->subtotal,
            'modifiers' => $this->modifiers->map(fn($mod) => [
                'id' => $mod->id,
                'name' => $mod->name,
                'price_adjustment' => (float) $mod->price_adjustment,
            ]),
            'note' => $this->note,
            'status' => $this->status,
            'void_reason' => $this->void_reason,
            'created_at' => $this->created_at,
        ];
    }
}
