<?php

namespace App\Services;

use App\Models\Product;

// Корзина хранится в сессии: [id товара => количество]
class Cart
{
    private const KEY = 'cart';

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(Product $product, int $qty = 1): void
    {
        $cart = $this->raw();
        $now = $cart[$product->id] ?? 0;
        // больше, чем есть на складе, положить нельзя
        $cart[$product->id] = min($now + $qty, $product->stock);
        session([self::KEY => $cart]);
    }

    public function set(Product $product, int $qty): void
    {
        $cart = $this->raw();
        if ($qty <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = min($qty, $product->stock);
        }
        session([self::KEY => $cart]);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    // строки корзины: товар, количество, сумма. Скрытые и удалённые товары пропускаем
    public function items(): array
    {
        $raw = $this->raw();
        if (!$raw) {
            return [];
        }

        $products = Product::active()->whereIn('id', array_keys($raw))->get()->keyBy('id');
        $rows = [];
        foreach ($raw as $id => $qty) {
            if (!isset($products[$id])) {
                continue;
            }
            $product = $products[$id];
            $rows[] = [
                'product' => $product,
                'qty' => $qty,
                'sum' => $product->price * $qty,
            ];
        }

        return $rows;
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function total(): float
    {
        return array_sum(array_column($this->items(), 'sum'));
    }
}
