<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

// открытые методы
Route::post('/register', [Api\AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [Api\AuthController::class, 'login'])->middleware('throttle:5,1');

Route::get('/categories', [Api\ProductController::class, 'categories']);
Route::get('/products', [Api\ProductController::class, 'index']);
Route::get('/products/{product}', [Api\ProductController::class, 'show'])->whereNumber('product');

// нужен токен
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [Api\AuthController::class, 'me']);
    Route::post('/logout', [Api\AuthController::class, 'logout']);

    Route::get('/orders', [Api\OrderController::class, 'index']);
    Route::post('/orders', [Api\OrderController::class, 'store']);
    Route::get('/orders/{order}', [Api\OrderController::class, 'show'])->whereNumber('order');

    // изменять товары может только админ
    Route::middleware('admin')->group(function () {
        Route::post('/products', [Api\ProductController::class, 'store']);
        Route::put('/products/{product}', [Api\ProductController::class, 'update'])->whereNumber('product');
        Route::delete('/products/{product}', [Api\ProductController::class, 'destroy'])->whereNumber('product');
    });
});
