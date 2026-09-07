<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function items(): Collection
    {
        $quantities = session()->get(self::SESSION_KEY, []);

        if (empty($quantities)) {
            return collect();
        }

        $variants = ProductVariant::with('product.brand')->whereIn('id', array_keys($quantities))->get();

        return $variants->map(function (ProductVariant $variant) use ($quantities) {
            return [
                'variant' => $variant,
                'quantity' => (int) $quantities[$variant->id],
                'line_total' => $variant->price * (int) $quantities[$variant->id],
            ];
        })->values();
    }

    public function add(ProductVariant $variant, int $quantity = 1): void
    {
        $cart = session()->get(self::SESSION_KEY, []);
        $cart[$variant->id] = min(99, ($cart[$variant->id] ?? 0) + max(1, $quantity));
        session()->put(self::SESSION_KEY, $cart);
    }

    public function update(int $variantId, int $quantity): void
    {
        $cart = session()->get(self::SESSION_KEY, []);

        if ($quantity <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId] = min(99, $quantity);
        }

        session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(int $variantId): void
    {
        $cart = session()->get(self::SESSION_KEY, []);
        unset($cart[$variantId]);
        session()->put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(session()->get(self::SESSION_KEY, []));
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    public function isEmpty(): bool
    {
        return empty(session()->get(self::SESSION_KEY, []));
    }
}