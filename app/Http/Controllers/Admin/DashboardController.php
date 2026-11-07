<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'orders' => Order::count(),
            'new_orders' => Order::where('status', 'new')->count(),
            // выручка без отменённых заказов
            'revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
            'products' => Product::count(),
            'users' => User::count(),
        ];

        $lastOrders = Order::with('user')->latest()->take(5)->get();
        $lowStock = Product::where('stock', '<', 10)->orderBy('stock')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'lastOrders', 'lowStock'));
    }
}
