<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->withCount('items')->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        // чужой заказ смотреть нельзя
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        // отменить можно только новый заказ, товар возвращается на склад
        if ($order->status !== 'new') {
            return back()->with('error', 'Этот заказ уже нельзя отменить');
        }

        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }
        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Заказ отменён');
    }
}
