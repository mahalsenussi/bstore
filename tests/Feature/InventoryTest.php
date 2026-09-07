<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;
use App\Services\InventoryService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@bstore.com')->firstOrFail();
    }

    private function inventory(int $stock = 10): Inventory
    {
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand-'.uniqid(), 'status' => true]);
        $store = Store::create([
            'brand_id' => $brand->id,
            'name' => 'Test Store '.uniqid(),
            'city' => 'Cairo',
            'address' => 'Test address',
            'status' => true,
        ]);
        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => 'Test Product '.uniqid(),
            'slug' => 'test-product-'.uniqid(),
            'status' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-'.uniqid(),
            'size' => 'M',
            'color' => 'Black',
            'price' => 100,
            'status' => true,
        ]);

        return Inventory::create([
            'store_id' => $store->id,
            'variant_id' => $variant->id,
            'stock_quantity' => $stock,
            'reserved_quantity' => 0,
            'reorder_level' => 5,
        ]);
    }

    public function test_inventory_pages_render(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/inventories')
            ->assertOk();

        $this->actingAs($this->admin())
            ->get('/admin/stock-transfers')
            ->assertOk();
    }

    public function test_adjust_increases_and_decreases_stock_and_logs_movements(): void
    {
        $inventory = $this->inventory(stock: 10);
        $service = app(InventoryService::class);

        $service->adjust($inventory, 5, 'receipt', 'Restock', null, $this->admin());
        $this->assertSame(15, $inventory->fresh()->stock_quantity);

        $service->adjust($inventory, -3, 'adjustment', 'Damaged unit', null, $this->admin());
        $this->assertSame(12, $inventory->fresh()->stock_quantity);

        $this->assertSame(2, $inventory->movements()->count());
        $this->assertSame(12, $inventory->movements()->first()->stock_after);
        $this->assertSame(-3, $inventory->movements()->first()->quantity);
    }

    public function test_adjust_never_drops_below_zero(): void
    {
        $inventory = $this->inventory(stock: 2);
        app(InventoryService::class)->adjust($inventory, -50, 'adjustment', 'Over-correction');

        $this->assertSame(0, $inventory->fresh()->stock_quantity);
    }

    public function test_transfer_approve_ships_stock_and_receive_adds_it(): void
    {
        $inventory = $this->inventory(stock: 10);
        $storeFrom = $inventory->store;
        $storeTo = Store::where('id', '!=', $storeFrom->id)->firstOrFail();
        $variant = $inventory->variant;

        $transfer = StockTransfer::create([
            'from_store_id' => $storeFrom->id,
            'to_store_id' => $storeTo->id,
            'variant_id' => $variant->id,
            'quantity' => 4,
            'status' => 'pending',
        ]);

        $service = app(InventoryService::class);

        $service->approveTransfer($transfer, $this->admin());
        $this->assertSame('in_transit', $transfer->fresh()->status);
        $this->assertSame(6, $inventory->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'type' => 'transfer_out',
            'quantity' => -4,
        ]);

        $service->receiveTransfer($transfer, $this->admin());
        $this->assertSame('received', $transfer->fresh()->status);
        $this->assertNotNull($transfer->fresh()->received_at);

        $received = Inventory::where('store_id', $storeTo->id)->where('variant_id', $variant->id)->firstOrFail();
        $this->assertSame(4, $received->stock_quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $received->id,
            'type' => 'transfer_in',
            'quantity' => 4,
        ]);
    }

    public function test_transfer_cannot_be_approved_without_stock(): void
    {
        $inventory = $this->inventory(stock: 1);
        $storeFrom = $inventory->store;
        $storeTo = Store::where('id', '!=', $storeFrom->id)->firstOrFail();
        $variant = $inventory->variant;

        $transfer = StockTransfer::create([
            'from_store_id' => $storeFrom->id,
            'to_store_id' => $storeTo->id,
            'variant_id' => $variant->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(InventoryService::class)->approveTransfer($transfer, $this->admin());
    }

    public function test_cancel_unshipped_transfer_requires_no_restock(): void
    {
        $inventory = $this->inventory(stock: 10);
        $storeFrom = $inventory->store;
        $storeTo = Store::where('id', '!=', $storeFrom->id)->firstOrFail();
        $variant = $inventory->variant;

        $transfer = StockTransfer::create([
            'from_store_id' => $storeFrom->id,
            'to_store_id' => $storeTo->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
            'status' => 'pending',
        ]);

        app(InventoryService::class)->cancelTransfer($transfer, 'Changed our minds');
        $this->assertSame('cancelled', $transfer->fresh()->status);
        $this->assertSame(10, $inventory->fresh()->stock_quantity);
        $this->assertStringContainsString('Changed our minds', $transfer->fresh()->notes);
    }

    public function test_cancel_in_transit_transfer_restores_stock(): void
    {
        $inventory = $this->inventory(stock: 10);
        $storeFrom = $inventory->store;
        $storeTo = Store::where('id', '!=', $storeFrom->id)->firstOrFail();
        $variant = $inventory->variant;

        $transfer = StockTransfer::create([
            'from_store_id' => $storeFrom->id,
            'to_store_id' => $storeTo->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
            'status' => 'pending',
        ]);

        $service = app(InventoryService::class);
        $service->approveTransfer($transfer, $this->admin());
        $this->assertSame(7, $inventory->fresh()->stock_quantity);

        $service->cancelTransfer($transfer, 'Stock needed back');
        $this->assertSame('cancelled', $transfer->fresh()->status);
        $this->assertSame(10, $inventory->fresh()->stock_quantity);
    }
}