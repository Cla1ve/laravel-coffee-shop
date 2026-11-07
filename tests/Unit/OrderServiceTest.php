<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $data = ['customer_name' => 'Иван', 'phone' => '+79991234567', 'address' => 'Москва, ул. Мира, 5'];

    public function test_total_is_sum_of_lines(): void
    {
        $a = Product::factory()->create(['price' => 120.50, 'stock' => 10]);
        $b = Product::factory()->create(['price' => 300, 'stock' => 10]);

        $order = (new OrderService())->create(User::factory()->create(), [$a->id => 2, $b->id => 1], $this->data);

        $this->assertEquals('541.00', $order->total);
        $this->assertCount(2, $order->items);
    }

    public function test_price_in_order_does_not_change_with_product(): void
    {
        $a = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $order = (new OrderService())->create(User::factory()->create(), [$a->id => 1], $this->data);

        $a->update(['price' => 999]);

        $this->assertEquals('100.00', $order->items()->first()->price);
    }

    public function test_not_enough_stock_throws_and_nothing_saved(): void
    {
        $a = Product::factory()->create(['stock' => 1]);
        $b = Product::factory()->create(['stock' => 10]);

        try {
            (new OrderService())->create(User::factory()->create(), [$b->id => 3, $a->id => 2], $this->data);
            $this->fail('Ожидалась ошибка');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('items', $e->errors());
        }

        $this->assertEquals(0, Order::count());
        $this->assertEquals(10, $b->fresh()->stock);
    }

    public function test_status_name(): void
    {
        $order = new Order(['status' => 'processing']);

        $this->assertEquals('В обработке', $order->status_name);
    }
}
