<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'table_no',
        'name',
        'capacity',
        'area_id',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function area()
    {
        return $this->belongsTo(TableArea::class, 'area_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function activeOrders()
    {
        return $this->hasMany(Order::class, 'table_id')
            ->whereNotIn('status', ['paid', 'cancelled', 'merged']);
    }

    public function allOrders()
    {
        return $this->hasMany(Order::class, 'table_id')->orderBy('created_at', 'desc');
    }

    public function transfersFrom()
    {
        return $this->hasMany(TableTransfer::class, 'from_table_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(TableTransfer::class, 'to_table_id');
    }

    public function mergeGroups()
    {
        return $this->belongsToMany(TableMerge::class, 'merge_group_tables', 'table_id', 'table_merge_id');
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->whereIn('status', ['occupied', 'ordering', 'payment']);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
