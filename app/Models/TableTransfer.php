<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableTransfer extends Model
{
    protected $table = 'table_transfers';

    protected $fillable = [
        'from_table_id',
        'to_table_id',
        'order_id',
        'user_id',
    ];

    public function fromTable()
    {
        return $this->belongsTo(Table::class, 'from_table_id');
    }

    public function toTable()
    {
        return $this->belongsTo(Table::class, 'to_table_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
