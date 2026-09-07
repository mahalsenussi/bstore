<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPES = [
        'receipt' => 'Receipt',
        'adjustment' => 'Adjustment',
        'transfer_out' => 'Transfer Out',
        'transfer_in' => 'Transfer In',
        'sale' => 'Sale',
        'sale_return' => 'Sale Return',
        'reserve' => 'Reserve',
        'release' => 'Release',
    ];

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'stock_after',
        'reason',
        'reference_type',
        'reference_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}