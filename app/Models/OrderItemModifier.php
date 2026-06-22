<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemModifier extends Model
{
    protected $table = 'order_item_modifiers';

    protected $fillable = [
        'order_item_id',
        'modifier_id',
        'name',
        'price_adjustment',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
        ];
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function modifier()
    {
        return $this->belongsTo(MenuItemModifier::class, 'modifier_id');
    }
}
