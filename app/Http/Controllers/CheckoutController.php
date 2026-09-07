<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function create()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $stores = Store::where('status', true)->get();

        return view('store.checkout', compact('items', 'subtotal', 'stores'));
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $items = $this->cart->items();
        $subtotal = round($this->cart->subtotal(), 2);

        $order = DB::transaction(function () use ($data, $items, $subtotal) {
            $customer = Customer::firstOrCreate(
                ['email' => $data['email'] ?? null],
                ['name' => $data['name'], 'phone' => $data['phone']]
            );

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('ymd') . '-' . strtoupper(uniqid()),
                'customer_id' => $customer->id,
                'store_id' => $data['store_id'] ?? null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'pending',
                'payment_method' => 'cash_on_delivery',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $line) {
                $order->items()->create([
                    'variant_id' => $line['variant']->id,
                    'qty' => $line['quantity'],
                    'price' => $line['variant']->price,
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        return redirect()->route('checkout.success', $order->id)->with('success', 'Order placed successfully!');
    }

    public function success(Order $order)
    {
        return view('store.success', compact('order'));
    }
}