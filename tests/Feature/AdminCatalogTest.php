<?php

namespace Tests\Feature;

use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\StoreResource\Pages\ListStores;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
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

    public function test_brands_page_renders(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->assertTrue($admin->hasPermissionTo('manage brands'), 'admin should have manage brands permission');
        $this->assertTrue(\App\Filament\Resources\BrandResource::canViewAny($admin), 'canViewAny should pass for admin');

        $this->get('/admin/brands')
            ->assertOk();
    }

    public function test_stores_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/stores')
            ->assertOk();
    }

    public function test_categories_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/categories')
            ->assertOk();
    }

    public function test_products_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/products')
            ->assertOk();
    }

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin/brands')->assertRedirect('/admin/login');
    }

    public function test_product_form_submits_with_variants(): void
    {
        $this->actingAs($this->admin());

        $product = \App\Models\Product::first();

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\EditProduct::class, ['record' => $product->getKey()])
            ->assertSuccessful()
            ->fillForm([
                'name' => $product->name,
                'slug' => $product->slug,
                'brand_id' => $product->brand_id,
                'status' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => $product->name]);
    }

    public function test_brand_create_form_creates_brand(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(\App\Filament\Resources\BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => 'Nike',
                'slug' => 'nike',
                'status' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', ['slug' => 'nike', 'name' => 'Nike']);
    }

    public function test_product_view_page_renders_relation_managers(): void
    {
        $this->actingAs($this->admin());

        $product = \App\Models\Product::first();

        $this->get(\App\Filament\Resources\ProductResource::getUrl('view', ['record' => $product]))
            ->assertOk();

        Livewire::test(\App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => \App\Filament\Resources\ProductResource\Pages\ViewProduct::class,
        ])
            ->assertSuccessful();
    }
}