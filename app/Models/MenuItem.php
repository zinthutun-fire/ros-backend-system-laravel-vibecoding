<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'category_id',
        'kitchen_id',
        'name',
        'price',
        'image',
        'description',
        'has_modifiers',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'has_modifiers' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function modifiers()
    {
        return $this->hasMany(MenuItemModifier::class);
    }

    public function activeModifiers()
    {
        return $this->hasMany(MenuItemModifier::class)->where('is_active', true);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeByKitchen($query, $kitchenId)
    {
        return $query->where('kitchen_id', $kitchenId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
