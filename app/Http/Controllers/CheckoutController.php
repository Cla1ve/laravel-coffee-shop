<?php

namespace App\Http\Controllers;

use App\Services\Cart;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function create(Cart $cart)
    {
        if (!$cart->items()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        return view('checkout.create', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }

    public function store(Request $request, Cart $cart, OrderService $orders)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|min:2|max:100',
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required|string|min:10|max:255',
            'comment' => 'nullable|string|max:500',
        ]);

        // в заказ идут только те товары, которые ещё доступны
        $lines = [];
        foreach ($cart->items() as $row) {
            $lines[$row['product']->id] = $row['qty'];
        }

        $order = $orders->create($request->user(), $lines, $data);
        $cart->clear();

        return redirect()->route('orders.show', $order)->with('success', 'Заказ оформлен');
    }
}
