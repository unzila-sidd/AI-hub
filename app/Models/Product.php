<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'price',
        'cost',
        'stock',
        'alert_stock',
        'description',
        'active',
    ];

    protected $casts = [
        'price' => 'float',
        'cost' => 'float',
        'stock' => 'integer',
        'alert_stock' => 'integer',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'alert_stock');
    }
}