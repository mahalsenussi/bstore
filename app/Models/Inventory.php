<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'variant_id',
        'stock_quantity',
        'reserved_quantity',
        'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'reorder_level' => 'integer',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class)->latest('id');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return $this->stock_quantity - $this->reserved_quantity;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }
}