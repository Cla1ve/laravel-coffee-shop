<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_and_count(): void
    {
        $a = Product::factory()->create(['price' => 100, 'stock' => 10]);
        $b = Product::factory()->create(['price' => 250, 'stock' => 10]);

        $cart = new Cart();
        $cart->add($a, 2);
        $cart->add($b, 1);

        $this->assertEquals(3, $cart->count());
        $this->assertEquals(450, $cart->total());
    }

    public function test_adding_same_product_sums_quantity(): void
    {
        $a = Product::factory()->create(['stock' => 10]);

        $cart = new Cart();
        $cart->add($a, 2);
        $cart->add($a, 3);

        $this->assertEquals([$a->id => 5], $cart->raw());
    }

    public function test_set_zero_removes_item(): void
    {
        $a = Product::factory()->create(['stock' => 10]);

        $cart = new Cart();
        $cart->add($a, 2);
        $cart->set($a, 0);

        $this->assertEmpty($cart->raw());
    }

    public function test_hidden_product_is_skipped(): void
    {
        $a = Product::factory()->create(['stock' => 10]);
        $cart = new Cart();
        $cart->add($a, 2);

        $a->update(['is_active' => false]);

        $this->assertEmpty($cart->items());
        $this->assertEquals(0, $cart->total());
    }
}
