<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_shows_only_active_products(): void
    {
        Product::factory()->create(['name' => 'Видимый кофе', 'slug' => 'visible']);
        Product::factory()->create(['name' => 'Скрытый кофе', 'slug' => 'hidden', 'is_active' => false]);

        $this->get('/catalog')->assertOk()->assertSee('Видимый кофе')->assertDontSee('Скрытый кофе');
    }

    public function test_search_by_name(): void
    {
        Product::factory()->create(['name' => 'Эфиопия Сидамо', 'slug' => 'a']);
        Product::factory()->create(['name' => 'Бразилия Сантос', 'slug' => 'b']);

        $this->get('/catalog?q=Сидамо')->assertSee('Эфиопия Сидамо')->assertDontSee('Бразилия Сантос');
    }

    public function test_search_with_sql_injection_text_does_not_break(): void
    {
        Product::factory()->create(['name' => 'Обычный товар', 'slug' => 'a']);

        $this->get("/catalog?q=" . urlencode("' OR 1=1 --"))->assertOk()->assertDontSee('Обычный товар');
        $this->assertDatabaseCount('products', 1);
    }

    public function test_filter_by_category(): void
    {
        $tea = Category::factory()->create(['name' => 'Чай', 'slug' => 'chai']);
        Product::factory()->create(['name' => 'Зелёный чай', 'slug' => 'a', 'category_id' => $tea->id]);
        Product::factory()->create(['name' => 'Турка', 'slug' => 'b']);

        $this->get('/catalog?category=chai')->assertSee('Зелёный чай')->assertDontSee('Турка');
    }

    public function test_filter_by_price(): void
    {
        Product::factory()->create(['name' => 'Дешёвый', 'slug' => 'a', 'price' => 100]);
        Product::factory()->create(['name' => 'Дорогой', 'slug' => 'b', 'price' => 5000]);

        $this->get('/catalog?min=1000')->assertSee('Дорогой')->assertDontSee('Дешёвый');
    }

    public function test_sort_by_price(): void
    {
        Product::factory()->create(['name' => 'Первый', 'slug' => 'a', 'price' => 900]);
        Product::factory()->create(['name' => 'Второй', 'slug' => 'b', 'price' => 100]);

        $this->get('/catalog?sort=price_asc')->assertSeeInOrder(['Второй', 'Первый']);
    }

    public function test_catalog_has_nine_products_per_page(): void
    {
        Product::factory()->count(12)->create();

        $this->get('/catalog')->assertOk();
        $this->assertCount(9, $this->get('/catalog')->viewData('products'));
        $this->assertCount(3, $this->get('/catalog?page=2')->viewData('products'));
    }

    public function test_bad_sort_value_gives_error(): void
    {
        $this->get('/catalog?sort=drop')->assertSessionHasErrors('sort');
    }

    public function test_product_page_opens(): void
    {
        $p = Product::factory()->create(['name' => 'Колумбия', 'slug' => 'kolumbiya']);

        $this->get('/product/kolumbiya')->assertOk()->assertSee('Колумбия');
    }

    public function test_hidden_product_page_is_404(): void
    {
        Product::factory()->create(['slug' => 'hidden', 'is_active' => false]);

        $this->get('/product/hidden')->assertNotFound();
    }

    public function test_review_text_is_escaped(): void
    {
        $p = Product::factory()->create(['slug' => 'p']);
        $u = User::factory()->create();
        Review::create(['user_id' => $u->id, 'product_id' => $p->id, 'rating' => 5, 'text' => '<script>alert(1)</script>']);

        $this->get('/product/p')->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }
}
