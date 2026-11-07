<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|min:2|max:100|unique:categories,name']);
        $data['slug'] = Str::slug($data['name']);

        Category::create($data);

        return back()->with('success', 'Категория добавлена');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:100|unique:categories,name,' . $category->id,
        ]);
        $data['slug'] = Str::slug($data['name']);

        $category->update($data);

        return back()->with('success', 'Категория сохранена');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'В категории есть товары, сначала перенесите или удалите их');
        }

        $category->delete();

        return back()->with('success', 'Категория удалена');
    }
}
