<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'table_id',
        'status',
        'total',
        'tax_total',
        'service_charge_total',
        'discount_total',
        'grand_total',
        'notes',
        'created_by',
        'paid_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'service_charge_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function activeItems()
    {
        return $this->hasMany(OrderItem::class)->where('status', '!=', 'voided');
    }

    public function kitchenItems($kitchenId)
    {
        return $this->hasMany(OrderItem::class)->where('kitchen_id', $kitchenId);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function transfers()
    {
        return $this->hasMany(TableTransfer::class);
    }

    public function merge()
    {
        return $this->hasOne(TableMerge::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['paid', 'cancelled', 'merged']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled', 'merged']);
    }

    public function canAddItems(): bool
    {
        return in_array($this->status, ['new', 'processing', 'completed']);
    }

    public function deriveStatus(): string
    {
        $statuses = $this->items->pluck('status')->unique();

        if ($statuses->isEmpty() || $statuses->every(fn($s) => $s === 'voided')) {
            return 'cancelled';
        }

        if ($statuses->every(fn($s) => $s === 'completed' || $s === 'voided')) {
            return 'completed';
        }

        if ($statuses->contains('started') || $statuses->contains('accepted')) {
            return 'processing';
        }

        if ($statuses->every(fn($s) => $s === 'pending')) {
            return 'new';
        }

        return $this->status;
    }
}
