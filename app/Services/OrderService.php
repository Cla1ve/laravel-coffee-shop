<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Создаёт заказ. $lines: [id товара => количество], $data: имя, телефон, адрес, комментарий.
     * Если товара не хватает, заказ не создаётся.
     */
    public function create(User $user, array $lines, array $data): Order
    {
        if (!$lines) {
            throw ValidationException::withMessages(['items' => 'Корзина пуста']);
        }

        return DB::transaction(function () use ($user, $lines, $data) {
            $products = Product::active()->whereIn('id', array_keys($lines))->lockForUpdate()->get()->keyBy('id');

            $total = 0;
            foreach ($lines as $id => $qty) {
                $product = $products[$id] ?? null;
                if (!$product) {
                    throw ValidationException::withMessages(['items' => "Товар №$id недоступен"]);
                }
                if ($qty < 1 || $qty > $product->stock) {
                    throw ValidationException::withMessages(['items' => "Товара «{$product->name}» осталось {$product->stock} шт."]);
                }
                $total += $product->price * $qty;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'new',
                'total' => $total,
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'comment' => $data['comment'] ?? null,
            ]);

            foreach ($lines as $id => $qty) {
                $order->items()->create([
                    'product_id' => $id,
                    'quantity' => $qty,
                    'price' => $products[$id]->price,
                ]);
                $products[$id]->decrement('stock', $qty);
            }

            return $order;
        });
    }
}
