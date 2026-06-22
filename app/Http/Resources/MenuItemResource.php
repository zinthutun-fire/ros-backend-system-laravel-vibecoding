<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'kitchen_id' => $this->kitchen_id,
            'kitchen' => new KitchenResource($this->whenLoaded('kitchen')),
            'name' => $this->name,
            'price' => (float) $this->price,
            'image' => $this->image,
            'description' => $this->description,
            'has_modifiers' => $this->has_modifiers,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'modifiers' => MenuItemModifierResource::collection($this->whenLoaded('activeModifiers')),
        ];
    }
}
