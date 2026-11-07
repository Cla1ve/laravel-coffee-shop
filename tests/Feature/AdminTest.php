<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function productData(Category $cat, array $extra = []): array
    {
        return array_merge([
            'category_id' => $cat->id,
            'name' => 'Новый кофе',
            'description' => 'Описание товара',
            'price' => 800,
            'stock' => 15,
            'is_active' => 1,
        ], $extra);
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_ordinary_user_gets_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/products')->assertForbidden();
        $this->actingAs($user)->post('/admin/products', [])->assertForbidden();
    }

    public function test_admin_sees_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Сводка');
    }

    public function test_admin_can_create_product(): void
    {
        $cat = Category::factory()->create();

        $this->actingAs($this->admin())->post('/admin/products', $this->productData($cat))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Новый кофе', 'slug' => 'novyy-kofe', 'stock' => 15]);
    }

    public function test_admin_can_upload_image(): void
    {
        Storage::fake('public');
        $cat = Category::factory()->create();

        $this->actingAs($this->admin())->post('/admin/products', $this->productData($cat, [
            'image' => UploadedFile::fake()->image('photo.jpg', 600, 600),
        ]));

        $product = Product::first();
        $this->assertStringStartsWith('storage/products/', $product->image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $product->image));
    }

    public function test_image_must_be_picture(): void
    {
        $cat = Category::factory()->create();

        $this->actingAs($this->admin())->post('/admin/products', $this->productData($cat, [
            'image' => UploadedFile::fake()->create('virus.php', 10, 'text/x-php'),
        ]))->assertSessionHasErrors('image');
    }

    public function test_product_validation(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', ['name' => 'ab', 'price' => -5, 'stock' => 'много'])
            ->assertSessionHasErrors(['category_id', 'name', 'description', 'price', 'stock']);
    }

    public function test_admin_can_update_product(): void
    {
        $cat = Category::factory()->create();
        $p = Product::factory()->create(['category_id' => $cat->id, 'slug' => 'old']);

        $this->actingAs($this->admin())->put("/admin/products/old", $this->productData($cat, ['name' => 'Переименован', 'price' => 999]))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['id' => $p->id, 'name' => 'Переименован', 'price' => 999]);
    }

    public function test_admin_can_delete_product(): void
    {
        $p = Product::factory()->create(['slug' => 'p']);

        $this->actingAs($this->admin())->delete('/admin/products/p');

        $this->assertDatabaseMissing('products', ['id' => $p->id]);
    }

    public function test_two_products_with_same_name(): void
    {
        $cat = Category::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/products', $this->productData($cat));
        $this->actingAs($admin)->post('/admin/products', $this->productData($cat))->assertRedirect();

        $this->assertDatabaseCount('products', 2);
    }

    public function test_name_without_letters_gets_default_slug(): void
    {
        $cat = Category::factory()->create();

        $this->actingAs($this->admin())->post('/admin/products', $this->productData($cat, ['name' => '!!!']));

        $this->assertDatabaseHas('products', ['slug' => 'tovar']);
    }

    public function test_product_from_orders_cannot_be_deleted_but_is_hidden(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p']);
        $order = Order::create(['user_id' => $user->id, 'total' => 100, 'customer_name' => 'A', 'phone' => '+79990000000', 'address' => 'Москва, улица 1']);
        $order->items()->create(['product_id' => $p->id, 'quantity' => 1, 'price' => 100]);

        $this->actingAs($this->admin())->delete('/admin/products/p')->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $p->id, 'is_active' => false]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $cat = Category::factory()->create();
        Product::factory()->create(['category_id' => $cat->id]);

        $this->actingAs($this->admin())->delete("/admin/categories/{$cat->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $cat = Category::factory()->create();

        $this->actingAs($this->admin())->delete("/admin/categories/{$cat->id}");

        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Чай', 'slug' => 'chai']);

        $this->actingAs($this->admin())->post('/admin/categories', ['name' => 'Чай'])->assertSessionHasErrors('name');
    }

    public function test_admin_changes_order_status(): void
    {
        $user = User::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'total' => 100, 'customer_name' => 'A', 'phone' => '+79990000000', 'address' => 'Москва, улица 1']);

        $this->actingAs($this->admin())->patch("/admin/orders/{$order->id}", ['status' => 'shipped']);

        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_wrong_order_status_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'total' => 100, 'customer_name' => 'A', 'phone' => '+79990000000', 'address' => 'Москва, улица 1']);

        $this->actingAs($this->admin())->patch("/admin/orders/{$order->id}", ['status' => 'abc'])->assertSessionHasErrors('status');
    }
}
