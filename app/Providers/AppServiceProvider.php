<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\Cart;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // постраничный вывод в стиле Bootstrap
        Paginator::useBootstrapFive();

        // в шапке сайта нужны число товаров в корзине и список категорий
        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', (new Cart())->count());
            $view->with('menuCategories', Category::orderBy('name')->get());
        });
    }
}
