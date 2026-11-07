<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // на главной: категории и 8 последних товаров
        $categories = Category::withCount(['products' => fn ($q) => $q->active()])->orderBy('name')->get();
        $products = Product::active()->with('category')->latest()->take(8)->get();

        return view('home', compact('categories', 'products'));
    }
}
