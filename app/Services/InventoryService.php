<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function adjust(Inventory $inventory, int $delta, string $type, ?string $reason = null, ?object $reference = null, ?User $user = null): InventoryMovement
    {
        return DB::transaction(function () use ($inventory, $delta, $type, $reason, $reference, $user) {
            $inventory->lockForUpdate();

            $inventory->stock_quantity = max(0, $inventory->stock_quantity + $delta);
            $inventory->save();

            return $inventory->movements()->create([
                'type' => $type,
                'quantity' => $delta,
                'stock_after' => $inventory->stock_quantity,
                'reason' => $reason,
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'reference_id' => $reference ? $reference->id : null,
                'user_id' => $user?->id,
            ]);
        });
    }

    public function reserve(Inventory $inventory, int $quantity, ?string $reason = null, ?object $reference = null, ?User $user = null): InventoryMovement
    {
        return DB::transaction(function () use ($inventory, $quantity, $reason, $reference, $user) {
            $inventory->lockForUpdate();

            $stockLevel = $inventory->available_quantity;
            $reserve = min($quantity, $stockLevel);

            $inventory->reserved_quantity += $reserve;
            $inventory->save();

            return $inventory->movements()->create([
                'type' => 'reserve',
                'quantity' => $reserve,
                'stock_after' => $inventory->stock_quantity - $inventory->reserved_quantity,
                'reason' => $reason,
                'reference_type' => $reference ? $reference->getMorphClass() : null,
                'reference_id' => $reference ? $reference->id : null,
                'user_id' => $user?->id,
            ]);
        });
    }

    public function approveTransfer(StockTransfer $transfer, ?User $user = null): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $user) {
            abort_if($transfer->status !== 'pending', 422, 'Only pending transfers can be approved.');

            $from = Inventory::where('store_id', $transfer->from_store_id)
                ->where('variant_id', $transfer->variant_id)
                ->firstOrFail();

            if ($from->stock_quantity < $transfer->quantity) {
                abort(422, 'Insufficient stock in the source store.');
            }

            $transfer->status = 'in_transit';
            $transfer->approved_by = $user?->id;
            $transfer->approved_at = now();
            $transfer->in_transit_at = now();
            $transfer->save();

            $this->adjust($from, -$transfer->quantity, 'transfer_out', 'Stock transfer #'.$transfer->id.' -> '.$transfer->toStore->name, $transfer, $user);

            return $transfer;
        });
    }

    public function receiveTransfer(StockTransfer $transfer, ?User $user = null): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $user) {
            abort_if(! in_array($transfer->status, ['approved', 'in_transit']), 422, 'Transfer must be approved or in transit to receive.');

            $to = Inventory::firstOrCreate(
                [
                    'store_id' => $transfer->to_store_id,
                    'variant_id' => $transfer->variant_id,
                ],
                [
                    'stock_quantity' => 0,
                    'reserved_quantity' => 0,
                    'reorder_level' => 5,
                ]
            );

            $this->adjust($to, $transfer->quantity, 'transfer_in', 'Stock transfer #'.$transfer->id.' <- '.$transfer->fromStore->name, $transfer, $user);

            $transfer->status = 'received';
            $transfer->received_by = $user?->id;
            $transfer->received_at = now();
            $transfer->save();

            return $transfer;
        });
    }

    public function cancelTransfer(StockTransfer $transfer, ?string $reason = null, ?User $user = null): StockTransfer
    {
        return DB::transaction(function () use ($transfer, $reason, $user) {
            abort_if(in_array($transfer->status, ['received', 'cancelled']), 422, 'Transfer cannot be cancelled in its current state.');

            if ($transfer->status === 'in_transit') {
                $this->restoreTransferStock($transfer, $user);
            }

            $transfer->status = 'cancelled';
            $transfer->cancelled_at = now();
            $transfer->notes = trim(($transfer->notes ? $transfer->notes."\n" : '').'Cancelled: '.($reason ?? 'No reason provided.'));
            $transfer->save();

            return $transfer;
        });
    }

    private function restoreTransferStock(StockTransfer $transfer, ?User $user = null): void
    {
        $from = Inventory::where('store_id', $transfer->from_store_id)
            ->where('variant_id', $transfer->variant_id)
            ->first();

        if ($from) {
            $this->adjust($from, $transfer->quantity, 'adjustment', 'Restored after cancellation of transfer #'.$transfer->id, $transfer, $user);
        }
    }
}