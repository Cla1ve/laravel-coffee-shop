<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items.product')->latest()->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return new OrderResource($order->load('items.product'));
    }

    public function store(Request $request, OrderService $orders)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|min:2|max:100',
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'address' => 'required|string|min:10|max:255',
            'comment' => 'nullable|string|max:500',
            'items' => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:99',
        ]);

        // собираем строки заказа: id товара => количество
        $lines = [];
        foreach ($data['items'] as $item) {
            $id = $item['product_id'];
            $lines[$id] = ($lines[$id] ?? 0) + $item['quantity'];
        }

        $order = $orders->create($request->user(), $lines, $data);

        return (new OrderResource($order->load('items.product')))->response()->setStatusCode(201);
    }
}
