<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'in_transit' => 'In Transit',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'from_store_id',
        'to_store_id',
        'variant_id',
        'quantity',
        'status',
        'notes',
        'requested_by',
        'approved_by',
        'received_by',
        'approved_at',
        'in_transit_at',
        'received_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'approved_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'received_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function fromStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}