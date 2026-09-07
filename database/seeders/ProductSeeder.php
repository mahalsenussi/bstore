<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $skechers = Brand::where('slug', 'skechers')->firstOrFail();
        $okaid = Brand::where('slug', 'okaid')->firstOrFail();

        $footwear = Category::firstOrCreate(
            ['slug' => 'footwear'],
            ['name' => 'Footwear', 'status' => true]
        );
        $apparel = Category::firstOrCreate(
            ['slug' => 'apparel'],
            ['name' => 'Apparel', 'status' => true]
        );

        $mensShoes = $this->category('Men\'s Shoes', $footwear);
        $womensShoes = $this->category('Women\'s Shoes', $footwear);
        $kidsShoes = $this->category('Kids\' Shoes', $footwear);
        $kidsClothing = $this->category('Kids\' Clothing', $apparel);

        $this->createProduct($skechers, $mensShoes, 'Skechers GOwalk 6', 'Breathable knit uppers with a responsive 5GEN midsole for all-day comfort.', [
            ['size' => '40', 'color' => 'Grey', 'price' => 3290, 'compare' => 3990],
            ['size' => '41', 'color' => 'Grey', 'price' => 3290, 'compare' => 3990],
            ['size' => '42', 'color' => 'Grey', 'price' => 3290, 'compare' => 3990],
            ['size' => '43', 'color' => 'Black', 'price' => 3290, 'compare' => 3990],
        ]);

        $this->createProduct($skechers, $womensShoes, 'Skechers D\'Lites', 'Retro-inspired styling with a cushioned Memory Foam insole and subtle platform.', [
            ['size' => '36', 'color' => 'White/Pink', 'price' => 3590, 'compare' => 4290],
            ['size' => '37', 'color' => 'White/Pink', 'price' => 3590, 'compare' => 4290],
            ['size' => '38', 'color' => 'White/Pink', 'price' => 3590, 'compare' => 4290],
            ['size' => '39', 'color' => 'Black', 'price' => 3590, 'compare' => 4290],
        ]);

        $this->createProduct($skechers, $kidsShoes, 'Skechers Kids Flex-Glow', 'Light-up designs and an adjustable strap make every step fun for little ones.', [
            ['size' => '27', 'color' => 'Blue', 'price' => 1890, 'compare' => null],
            ['size' => '28', 'color' => 'Blue', 'price' => 1890, 'compare' => null],
            ['size' => '29', 'color' => 'Pink', 'price' => 1890, 'compare' => null],
        ]);

        $this->createProduct($okaid, $kidsClothing, 'Okaïdi Boy\'s Cotton T-Shirt', 'Soft organic cotton tee with a playful print. Machine washable and pill-resistant.', [
            ['size' => '4Y', 'color' => 'Navy', 'price' => 349, 'compare' => 449],
            ['size' => '5Y', 'color' => 'Navy', 'price' => 349, 'compare' => 449],
            ['size' => '6Y', 'color' => 'Navy', 'price' => 349, 'compare' => 449],
            ['size' => '4Y', 'color' => 'White', 'price' => 349, 'compare' => 449],
            ['size' => '5Y', 'color' => 'White', 'price' => 349, 'compare' => 449],
        ]);

        $this->createProduct($okaid, $kidsClothing, 'Okaïdi Girl\'s Denim Dress', 'Comfortable stretch-denim dress with a charming button front and soft lining.', [
            ['size' => '3A', 'color' => 'Denim', 'price' => 649, 'compare' => 799],
            ['size' => '4A', 'color' => 'Denim', 'price' => 649, 'compare' => 799],
            ['size' => '5A', 'color' => 'Denim', 'price' => 649, 'compare' => 799],
        ]);

        $this->createProduct($okaid, $kidsClothing, 'Okaïdi Boys Slim Jeans', 'A comfy regular-fit jean that keeps its shape wash after wash.', [
            ['size' => '5Y', 'color' => 'Blue', 'price' => 549, 'compare' => 699],
            ['size' => '6Y', 'color' => 'Blue', 'price' => 549, 'compare' => 699],
            ['size' => '7Y', 'color' => 'Blue', 'price' => 549, 'compare' => 699],
        ]);
    }

    private function category(string $name, Category $parent): Category
    {
        return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'parent_id' => $parent->id, 'status' => true]
        );
    }

    private function createProduct(Brand $brand, Category $category, string $name, string $description, array $variants): void
    {
        $product = Product::firstOrCreate(
            ['slug' => Str::slug($name)],
            [
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'name' => $name,
                'description' => $description,
                'status' => true,
            ]
        );

        foreach ($variants as $i => $variant) {
            $sku = strtoupper(Str::slug($brand->name, '_')).'-'.Str::upper(Str::slug($name, '_')).'-'.$variant['size'].'-'.$variant['color'];

            ProductVariant::firstOrCreate(
                ['sku' => $sku],
                [
                    'product_id' => $product->id,
                    'size' => $variant['size'],
                    'color' => $variant['color'],
                    'price' => $variant['price'],
                    'compare_at_price' => $variant['compare'],
                    'barcode' => '6'.str_pad((string) ($product->id * 1000 + $i + 1), 11, '0', STR_PAD_LEFT),
                    'status' => true,
                ]
            );
        }
    }
}