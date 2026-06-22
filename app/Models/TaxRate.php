<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $table = 'tax_rates';

    protected $fillable = [
        'name',
        'rate',
        'type',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public static function getDefaultTaxRate(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->where('type', 'tax')
            ->first();
    }

    public static function getDefaultServiceCharge(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->where('type', 'service_charge')
            ->first();
    }
}
