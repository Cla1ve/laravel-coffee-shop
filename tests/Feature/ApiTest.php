<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_list_has_pagination_and_fields(): void
    {
        Product::factory()->count(12)->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'price', 'stock', 'category' => ['id', 'name']]], 'links', 'meta']);
    }

    public function test_products_filter_by_category_and_price(): void
    {
        $tea = Category::factory()->create(['slug' => 'chai']);
        Product::factory()->create(['category_id' => $tea->id, 'price' => 300]);
        Product::factory()->create(['category_id' => $tea->id, 'price' => 3000]);
        Product::factory()->create(['price' => 300]);

        $this->getJson('/api/products?category=chai&min=1000')->assertJsonCount(1, 'data');
    }

    public function test_per_page_limit(): void
    {
        $this->getJson('/api/products?per_page=1000')->assertStatus(422);
    }

    public function test_product_by_id(): void
    {
        $p = Product::factory()->create(['name' => 'Колумбия']);

        $this->getJson("/api/products/{$p->id}")->assertOk()->assertJsonPath('data.name', 'Колумбия');
    }

    public function test_not_found_message_has_no_class_names(): void
    {
        $this->getJson('/api/products/9999')->assertNotFound()->assertExactJson(['message' => 'Не найдено']);
    }

    public function test_hidden_product_is_404(): void
    {
        $p = Product::factory()->create(['is_active' => false]);

        $this->getJson("/api/products/{$p->id}")->assertNotFound();
    }

    public function test_categories_list(): void
    {
        Category::factory()->count(3)->create();

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(3);
    }

    public function test_register_returns_token(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Анна', 'email' => 'anna@example.com', 'password' => 'Secret123', 'password_confirmation' => 'Secret123',
        ])->assertCreated()->assertJsonStructure(['user' => ['id', 'email'], 'token'])->assertJsonMissing(['password']);
    }

    public function test_login_and_me(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $token = $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'password1'])
            ->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJson(['email' => 'a@example.com']);
    }

    public function test_login_wrong_password_gives_401(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'bad'])->assertUnauthorized();
    }

    public function test_orders_need_token(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
        $this->postJson('/api/orders', [])->assertUnauthorized();
    }

    public function test_create_order_by_api(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['price' => 500, 'stock' => 5]);
        Sanctum::actingAs($user);

        $this->postJson('/api/orders', [
            'customer_name' => 'Иван', 'phone' => '+79991234567', 'address' => 'Москва, ул. Мира, 5',
            'items' => [['product_id' => $p->id, 'quantity' => 2]],
        ])->assertCreated()->assertJsonPath('data.total', '1000.00');

        $this->assertEquals(3, $p->fresh()->stock);
    }

    public function test_same_product_twice_is_summed(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['price' => 100, 'stock' => 5]);
        Sanctum::actingAs($user);

        $this->postJson('/api/orders', [
            'customer_name' => 'Иван', 'phone' => '+79991234567', 'address' => 'Москва, ул. Мира, 5',
            'items' => [['product_id' => $p->id, 'quantity' => 2], ['product_id' => $p->id, 'quantity' => 4]],
        ])->assertStatus(422);

        $this->assertEquals(5, $p->fresh()->stock);
    }

    public function test_order_with_unknown_product(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/orders', [
            'customer_name' => 'Иван', 'phone' => '+79991234567', 'address' => 'Москва, ул. Мира, 5',
            'items' => [['product_id' => 9999, 'quantity' => 1]],
        ])->assertStatus(422);
    }

    public function test_foreign_order_is_forbidden(): void
    {
        $owner = User::factory()->create();
        $order = Order::create(['user_id' => $owner->id, 'total' => 1, 'customer_name' => 'A', 'phone' => '+79990000000', 'address' => 'Москва, улица 1']);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/orders/{$order->id}")->assertForbidden();
    }

    public function test_only_admin_changes_products(): void
    {
        $cat = Category::factory()->create();
        $data = ['category_id' => $cat->id, 'name' => 'Через API', 'description' => 'Описание', 'price' => 100, 'stock' => 3];

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/products', $data)->assertForbidden();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->postJson('/api/products', $data)->assertCreated()->assertJsonPath('data.name', 'Через API');
    }

    public function test_admin_updates_and_deletes_product(): void
    {
        $cat = Category::factory()->create();
        $p = Product::factory()->create(['category_id' => $cat->id]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->putJson("/api/products/{$p->id}", ['category_id' => $cat->id, 'name' => 'Другое имя', 'description' => 'Описание', 'price' => 250, 'stock' => 1])
            ->assertOk()->assertJsonPath('data.price', '250.00');

        $this->deleteJson("/api/products/{$p->id}")->assertNoContent();
    }

    public function test_product_from_orders_is_hidden_not_deleted(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'total' => 1, 'customer_name' => 'A', 'phone' => '+79990000000', 'address' => 'Москва, улица 1']);
        $order->items()->create(['product_id' => $p->id, 'quantity' => 1, 'price' => 1]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->deleteJson("/api/products/{$p->id}")->assertStatus(409);

        $this->assertDatabaseHas('products', ['id' => $p->id, 'is_active' => false]);
    }

    public function test_logout_removes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('t')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
