<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id',
        'kitchen_id',
        'qty',
        'price',
        'subtotal',
        'note',
        'status',
        'void_reason',
        'voided_by',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function modifiers()
    {
        return $this->hasMany(OrderItemModifier::class);
    }

    public function scopeByKitchen($query, $kitchenId)
    {
        return $query->where('kitchen_id', $kitchenId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'voided');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function modifiersTotal(): float
    {
        return (float) $this->modifiers->sum('price_adjustment');
    }
}
