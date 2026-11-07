<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndOrderTest extends TestCase
{
    use RefreshDatabase;

    private array $orderData = [
        'customer_name' => 'Иван Петров',
        'phone' => '+79991234567',
        'address' => 'г. Москва, ул. Ленина, д. 1',
    ];

    public function test_guest_can_add_to_cart(): void
    {
        $p = Product::factory()->create(['slug' => 'p']);

        $this->post("/cart/{$p->slug}")->assertRedirect();
        $this->assertEquals([$p->id => 1], session('cart'));
    }

    public function test_cart_quantity_is_limited_by_stock(): void
    {
        $p = Product::factory()->create(['slug' => 'p', 'stock' => 3]);

        $this->post("/cart/p", ['qty' => 10]);

        $this->assertEquals(3, session('cart')[$p->id]);
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $p = Product::factory()->create(['slug' => 'p', 'stock' => 0]);

        $this->post('/cart/p')->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }

    public function test_cannot_add_hidden_product(): void
    {
        Product::factory()->create(['slug' => 'p', 'is_active' => false]);

        $this->post('/cart/p')->assertNotFound();
    }

    public function test_update_and_remove_from_cart(): void
    {
        $p = Product::factory()->create(['slug' => 'p']);
        $this->post('/cart/p');

        $this->patch('/cart/p', ['qty' => 4]);
        $this->assertEquals(4, session('cart')[$p->id]);

        $this->delete('/cart/p');
        $this->assertArrayNotHasKey($p->id, session('cart', []));
    }

    public function test_cart_page_shows_total(): void
    {
        $p = Product::factory()->create(['slug' => 'p', 'price' => 500]);
        $this->post('/cart/p', ['qty' => 3]);

        $this->get('/cart')->assertSee('1 500');
    }

    public function test_guest_is_sent_to_login_on_checkout(): void
    {
        $this->get('/checkout')->assertRedirect('/login');
    }

    public function test_order_is_created_and_stock_decreases(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p', 'price' => 700, 'stock' => 10]);

        $this->actingAs($user)->post('/cart/p', ['qty' => 2]);
        $response = $this->actingAs($user)->post('/checkout', $this->orderData);

        $order = Order::first();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertEquals('1400.00', $order->total);
        $this->assertEquals('new', $order->status);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => 2, 'price' => 700]);
        $this->assertEquals(8, $p->fresh()->stock);
        $this->assertEmpty(session('cart', []));
    }

    public function test_order_fails_when_stock_ended_after_adding_to_cart(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p', 'stock' => 5]);

        $this->actingAs($user)->post('/cart/p', ['qty' => 5]);
        $p->update(['stock' => 2]);

        $this->actingAs($user)->post('/checkout', $this->orderData)->assertSessionHasErrors('items');
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(2, $p->fresh()->stock);
    }

    public function test_empty_cart_cannot_be_ordered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/checkout')->assertRedirect('/cart');
        $this->actingAs($user)->post('/checkout', $this->orderData)->assertSessionHasErrors('items');
    }

    public function test_checkout_validates_fields(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['slug' => 'p']);
        $this->actingAs($user)->post('/cart/p');

        $this->actingAs($user)->post('/checkout', ['customer_name' => '', 'phone' => 'abc', 'address' => 'x'])
            ->assertSessionHasErrors(['customer_name', 'phone', 'address']);
    }

    public function test_user_sees_only_own_orders(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $orderB = Order::create(array_merge($this->orderData, ['user_id' => $b->id, 'total' => 100]));

        $this->actingAs($a)->get(route('orders.show', $orderB))->assertForbidden();
        $this->actingAs($b)->get(route('orders.show', $orderB))->assertOk();
    }

    public function test_cancel_returns_goods_to_stock(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p', 'stock' => 10]);
        $this->actingAs($user)->post('/cart/p', ['qty' => 4]);
        $this->actingAs($user)->post('/checkout', $this->orderData);
        $order = Order::first();

        $this->actingAs($user)->post(route('orders.cancel', $order));

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals(10, $p->fresh()->stock);
    }

    public function test_shipped_order_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        $order = Order::create(array_merge($this->orderData, ['user_id' => $user->id, 'total' => 100, 'status' => 'shipped']));

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHas('error');
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_cancelled_order_cannot_be_cancelled_twice(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p', 'stock' => 10]);
        $this->actingAs($user)->post('/cart/p', ['qty' => 4]);
        $this->actingAs($user)->post('/checkout', $this->orderData);
        $order = Order::first();

        $this->actingAs($user)->post(route('orders.cancel', $order));
        $this->actingAs($user)->post(route('orders.cancel', $order));

        $this->assertEquals(10, $p->fresh()->stock);
    }
}
