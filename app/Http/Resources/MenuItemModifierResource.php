<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemModifierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_item_id' => $this->menu_item_id,
            'name' => $this->name,
            'price_adjustment' => (float) $this->price_adjustment,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
