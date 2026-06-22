<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableMerge extends Model
{
    protected $table = 'table_merges';

    protected $fillable = [
        'group_code',
        'order_id',
        'merged_order_ids',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'merged_order_ids' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'merge_group_tables', 'table_merge_id', 'table_id');
    }

    public function mergeGroupTables()
    {
        return $this->hasMany(MergeGroupTable::class, 'table_merge_id');
    }
}
