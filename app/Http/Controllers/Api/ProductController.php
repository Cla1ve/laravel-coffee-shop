<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function categories()
    {
        return response()->json(Category::orderBy('name')->get(['id', 'name', 'slug']));
    }

    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:120',
            'sort' => 'nullable|in:new,price_asc,price_desc,name',
            'min' => 'nullable|numeric|min:0',
            'max' => 'nullable|numeric|min:0',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Product::active()->with('category');

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

        return ProductResource::collection($query->paginate($request->integer('per_page', 10))->withQueryString());
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product->load('category'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Product::uniqueSlug($data['name']);

        $product = Product::create($data);

        return (new ProductResource($product->load('category')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            $product->update(['is_active' => false]);

            return response()->json(['message' => 'Товар есть в заказах, он скрыт из каталога, но не удалён'], 409);
        }

        $product->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|min:3|max:150',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:1|max:1000000',
            'stock' => 'required|integer|min:0|max:100000',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
