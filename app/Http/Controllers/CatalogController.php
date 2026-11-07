<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:120',
            'sort' => 'nullable|in:new,price_asc,price_desc,name',
            'min' => 'nullable|numeric|min:0',
            'max' => 'nullable|numeric|min:0',
        ]);

        $query = Product::active()->with('category');

        // поиск по названию и описанию
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('description', 'like', "%$q%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $request->category));
        }
        if ($request->filled('min')) {
            $query->where('price', '>=', $request->min);
        }
        if ($request->filled('max')) {
            $query->where('price', '<=', $request->max);
        }

        match ($request->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest('id'),
        };

        // 9 товаров на странице, параметры фильтра сохраняются в ссылках страниц
        $products = $query->paginate(9)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('catalog.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'reviews.user']);
        $rating = $product->reviews->avg('rating');
        $myReview = auth()->check() ? $product->reviews->firstWhere('user_id', auth()->id()) : null;

        return view('catalog.show', compact('product', 'rating', 'myReview'));
    }
}
