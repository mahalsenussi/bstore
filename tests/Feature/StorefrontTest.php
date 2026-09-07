<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_renders(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('New product')
            ->assertSee(self::Skechers()->name);
    }

    public function test_shop_page_lists_active_products(): void
    {
        $this->get(route('shop'))
            ->assertOk()
            ->assertSee(self::Skechers()->name);
    }

    public function test_brand_page_renders(): void
    {
        $this->get(route('shop.brand', self::Skechers()->slug))
            ->assertOk()
            ->assertSee(self::Skechers()->name);
    }

    public function test_category_page_renders(): void
    {
        $category = Category::first();

        $this->get(route('shop.category', $category->slug))
            ->assertOk()
            ->assertSee($category->name);
    }

    public function test_product_page_renders_with_add_to_cart_form(): void
    {
        $product = Product::first();

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Add to cart');
    }

    public function test_inactive_product_is_not_visible(): void
    {
        $product = Product::create([
            'brand_id' => self::Skechers()->id,
            'category_id' => Category::first()->id,
            'name' => 'Hidden Product',
            'slug' => 'hidden-product-'.uniqid(),
            'status' => false,
        ]);

        $this->get(route('product.show', $product->slug))->assertNotFound();
    }

    public function test_search_finds_products(): void
    {
        $product = Product::first();

        $this->get(route('search', ['q' => $product->name]))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_cart_add_update_remove_flow(): void
    {
        $product = Product::first();
        $variant = ProductVariant::where('product_id', $product->id)->first();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->name);

        $this->post(route('cart.update'), ['quantity' => [$variant->id => 5]])
            ->assertRedirect(route('cart.index'));

        $this->post(route('cart.remove'), ['variant_id' => $variant->id])
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Your cart is empty');
    }

    public function test_checkout_creates_order_and_clears_cart(): void
    {
        $product = Product::first();
        $variant = ProductVariant::where('product_id', $product->id)->first();

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);

        $this->post(route('checkout.store'), [
            'name' => 'Ahmed Test',
            'phone' => '01000000000',
            'email' => 'ahmed@test.com',
            'city' => 'Cairo',
            'notes' => 'Ring the bell',
        ])->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertSame((int) round($variant->price * 3, 2) * 100, (int) round($order->total, 2) * 100);
        $this->assertSame(1, $order->items()->count());
        $this->assertNotNull(Customer::where('email', 'ahmed@test.com')->first());

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Your cart is empty');

        $this->get(route('checkout.success', $order->id))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_checkout_rejects_empty_cart(): void
    {
        $this->post(route('checkout.store'), [
            'name' => 'Ahmed Test',
            'phone' => '01000000000',
        ])->assertSessionHas('error', 'Your cart is empty.');

        $this->assertDatabaseCount('orders', 0);
    }

    private static function Skechers(): Brand
    {
        return Brand::where('slug', 'skechers')->first();
    }
}