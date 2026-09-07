<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class StorefrontController extends Controller
{
    public function home()
    {
        $brands = Brand::withCount('products')
            ->with(['products' => fn ($q) => $q->where('status', true)->with('coverImage')])
            ->where('status', true)
            ->get();
        $categories = Category::whereNull('parent_id')->withCount('products')->where('status', true)->get();
        $newProducts = Product::with(['brand', 'coverImage', 'variants'])
            ->where('status', true)
            ->latest()
            ->limit(8)
            ->get();

        return view('store.home', compact('brands', 'categories', 'newProducts'));
    }

    public function shop()
    {
        $products = Product::query()
            ->with(['brand', 'coverImage', 'variants', 'category'])
            ->where('status', true);

        if ($priceMin = request('min')) {
            $products->whereHas('variants', fn ($q) => $q->where('price', '>=', $priceMin));
        }
        if ($priceMax = request('max')) {
            $products->whereHas('variants', fn ($q) => $q->where('price', '<=', $priceMax));
        }
        if ($brand = request('brand')) {
            $products->whereHas('brand', fn ($q) => $q->where('slug', $brand));
        }
        if ($category = request('category')) {
            $category = Category::where('slug', $category)->first();
            if ($category) {
                $products->whereIn('category_id', $category->children->pluck('id')->push($category->id));
            }
        }

        $products = $products->paginate(12)->withQueryString();

        $brands = Brand::withCount('products')->where('status', true)->get();
        $categories = Category::with('children')->whereNull('parent_id')->where('status', true)->get();

        return view('store.shop', compact('products', 'brands', 'categories'));
    }

    public function brand(Brand $brand)
    {
        $products = $brand->products()
            ->with(['coverImage', 'variants', 'category'])
            ->where('status', true)
            ->when(request('min'), fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('price', '>=', request('min'))))
            ->when(request('max'), fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('price', '<=', request('max'))))
            ->paginate(12)
            ->withQueryString();

$brands = Brand::withCount('products')->where('status', true)->get();
        $categories = Category::with('children')->whereNull('parent_id')->where('status', true)->get();

        return view('store.shop', compact('products', 'brands', 'categories', 'brand'));
    }

    public function category(Category $category)
    {
        $categoryIds = $category->children->pluck('id')->push($category->id);

        $products = Product::query()
            ->with(['brand', 'coverImage', 'variants', 'category'])
            ->where('status', true)
            ->whereIn('category_id', $categoryIds)
            ->when(request('min'), fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('price', '>=', request('min'))))
            ->when(request('max'), fn ($q) => $q->whereHas('variants', fn ($v) => $v->where('price', '<=', request('max'))))
            ->paginate(12)
            ->withQueryString();

        $brands = Brand::withCount('products')->where('status', true)->get();
        $categories = Category::with('children')->whereNull('parent_id')->where('status', true)->get();

        return view('store.shop', compact('products', 'brands', 'categories', 'category'));
    }

    public function product(Product $product)
    {
        abort_unless($product->status, 404);

        $product->load(['brand', 'category', 'images', 'variants' => fn ($q) => $q->where('status', true)]);

        $related = Product::query()
            ->with(['coverImage', 'brand'])
            ->where('status', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('brand_id', $product->brand_id);
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('store.product', compact('product', 'related'));
    }

    public function search()
    {
        $query = trim(request('q'));

        $products = Product::query()
            ->with(['brand', 'coverImage', 'variants', 'category'])
            ->where('status', true)
            ->when($query, fn ($q) => $q->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', "%{$query}%")->orWhere('barcode', 'like', "%{$query}%"));
            }))
            ->paginate(12)
            ->withQueryString();

        $brands = Brand::withCount('products')->where('status', true)->get();
        $categories = Category::with('children')->whereNull('parent_id')->where('status', true)->get();

        return view('store.shop', compact('products', 'brands', 'categories', 'query'));
    }
}