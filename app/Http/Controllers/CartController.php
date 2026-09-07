<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();

        return view('store.cart', compact('items', 'subtotal'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'size' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $variant = ProductVariant::where('product_id', $data['product_id'])
            ->where('status', true)
            ->when($data['size'] ?? null, fn ($q, $size) => $q->where('size', $size))
            ->when($data['color'] ?? null, fn ($q, $color) => $q->where('color', $color))
            ->first();

        abort_unless($variant, 422, 'This item combination is not available.');

        $this->cart->add($variant, $data['quantity']);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item added to your cart.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'quantity' => ['required', 'array'],
            'quantity.*' => ['integer', 'min:0', 'max:99'],
        ]);

        foreach ($data['quantity'] as $variantId => $quantity) {
            $this->cart->update((int) $variantId, (int) $quantity);
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $request->validate(['variant_id' => ['required', 'integer']]);

        $this->cart->remove((int) $request->variant_id);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }
}