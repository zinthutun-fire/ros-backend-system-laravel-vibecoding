<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'amount',
        'cash_portion',
        'card_portion',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cash_portion' => 'decimal:2',
            'card_portion' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('paid_at', today());
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('paid_at', [$from, $to]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
