<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeMerge = null;
        if ($this->relationLoaded('mergeGroups')) {
            $activeMerge = $this->mergeGroups->first(fn($mg) => $mg->order?->isActive());
        }

        return [
            'id' => $this->id,
            'table_no' => $this->table_no,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'area' => new TableAreaResource($this->whenLoaded('area')),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'active_orders' => OrderResource::collection($this->whenLoaded('activeOrders')),
            'orders' => OrderResource::collection($this->whenLoaded('allOrders')),
            'is_merged' => $activeMerge !== null,
            'merged_group_code' => $activeMerge?->group_code,
            'merged_with_tables' => $activeMerge ? $activeMerge->tables->pluck('table_no')->toArray() : [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
