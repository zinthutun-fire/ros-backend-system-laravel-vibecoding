<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableArea extends Model
{
    protected $table = 'table_areas';

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function tables()
    {
        return $this->hasMany(Table::class, 'area_id');
    }
}
