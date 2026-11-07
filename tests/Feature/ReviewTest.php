<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_leave_review(): void
    {
        $p = Product::factory()->create(['slug' => 'p']);

        $this->post('/product/p/reviews', ['rating' => 5, 'text' => 'Хороший товар'])->assertRedirect('/login');
    }

    public function test_user_can_leave_review(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p']);

        $this->actingAs($user)->post('/product/p/reviews', ['rating' => 5, 'text' => 'Хороший товар']);

        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'product_id' => $p->id, 'rating' => 5]);
    }

    public function test_second_review_updates_first(): void
    {
        $user = User::factory()->create();
        $p = Product::factory()->create(['slug' => 'p']);

        $this->actingAs($user)->post('/product/p/reviews', ['rating' => 5, 'text' => 'Хороший товар']);
        $this->actingAs($user)->post('/product/p/reviews', ['rating' => 3, 'text' => 'Потом передумал']);

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', ['rating' => 3]);
    }

    public function test_rating_must_be_from_one_to_five(): void
    {
        $user = User::factory()->create();
        Product::factory()->create(['slug' => 'p']);

        $this->actingAs($user)->post('/product/p/reviews', ['rating' => 6, 'text' => 'Хороший товар'])->assertSessionHasErrors('rating');
        $this->actingAs($user)->post('/product/p/reviews', ['rating' => 0, 'text' => 'Хороший товар'])->assertSessionHasErrors('rating');
    }

    public function test_user_cannot_delete_foreign_review(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $p = Product::factory()->create();
        $review = Review::create(['user_id' => $a->id, 'product_id' => $p->id, 'rating' => 4, 'text' => 'Нормально']);

        $this->actingAs($b)->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->actingAs($a)->delete(route('reviews.destroy', $review));
        $this->assertDatabaseCount('reviews', 0);
    }
}
