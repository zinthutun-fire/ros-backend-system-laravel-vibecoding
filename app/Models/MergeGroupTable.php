<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergeGroupTable extends Model
{
    protected $table = 'merge_group_tables';

    protected $fillable = [
        'table_merge_id',
        'table_id',
    ];

    public function tableMerge()
    {
        return $this->belongsTo(TableMerge::class, 'table_merge_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }
}
