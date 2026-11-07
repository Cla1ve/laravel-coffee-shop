<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Cart $cart)
    {
        return view('cart.index', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }

    public function add(Request $request, Product $product, Cart $cart)
    {
        abort_unless($product->is_active, 404);

        $data = $request->validate(['qty' => 'nullable|integer|min:1|max:99']);

        if ($product->stock < 1) {
            return back()->with('error', 'Товара нет в наличии');
        }

        $cart->add($product, $data['qty'] ?? 1);

        return back()->with('success', 'Товар добавлен в корзину');
    }

    public function update(Request $request, Product $product, Cart $cart)
    {
        $data = $request->validate(['qty' => 'required|integer|min:0|max:99']);
        $cart->set($product, $data['qty']);

        return back();
    }

    public function remove(Product $product, Cart $cart)
    {
        $cart->remove($product);

        return back()->with('success', 'Товар удалён из корзины');
    }
}
