<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $service = app(InventoryService::class);
        $user = \App\Models\User::where('email', 'admin@bstore.com')->first();

        $stores = Store::all();
        $variants = ProductVariant::all();

        foreach ($stores as $store) {
            foreach ($variants as $index => $variant) {
                $qty = match (true) {
                    $variant->product->brand->slug === 'skechers' => [12, 18, 25, 4, 0][$index % 5],
                    default => [9, 15, 3, 20, 7][$index % 5],
                };

                $inventory = Inventory::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'variant_id' => $variant->id,
                    ],
                    [
                        'stock_quantity' => 0,
                        'reserved_quantity' => 0,
                        'reorder_level' => 5,
                    ]
                );

                if ($inventory->wasRecentlyCreated) {
                    $service->adjust($inventory, $qty, 'receipt', 'Initial stock seeding', null, $user);
                }
            }
        }
    }
}