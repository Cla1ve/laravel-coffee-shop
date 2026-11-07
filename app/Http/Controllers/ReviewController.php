<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'text' => 'required|string|min:5|max:1000',
        ]);

        // если отзыв уже есть, он обновляется
        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            $data
        );

        return back()->with('success', 'Отзыв сохранён');
    }

    public function destroy(Request $request, Review $review)
    {
        // удалить может автор или админ
        abort_unless($review->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $review->delete();

        return back()->with('success', 'Отзыв удалён');
    }
}
