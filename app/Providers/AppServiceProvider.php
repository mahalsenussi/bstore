<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        View::composer('store.*', function ($view) {
            $cart = app(CartService::class);

            $view->with([
                'cartCount' => $cart->count(),
                'navCategories' => Category::query()
                    ->where('status', true)
                    ->whereNull('parent_id')
                    ->with('children')
                    ->get(),
                'navBrands' => Brand::query()->where('status', true)->get(),
            ]);
        });
    }
}
