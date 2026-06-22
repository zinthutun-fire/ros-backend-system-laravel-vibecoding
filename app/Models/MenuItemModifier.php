<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemModifier extends Model
{
    protected $table = 'menu_item_modifiers';

    protected $fillable = [
        'menu_item_id',
        'name',
        'price_adjustment',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
