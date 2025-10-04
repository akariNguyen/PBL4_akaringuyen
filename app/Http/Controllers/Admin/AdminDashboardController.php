<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Shop;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $userCount    = User::count();
        $sellerCount  = User::where('role', 'seller')->count();
        $productCount = Product::count();
        $pendingProducts = Product::where('status', 'pending')->count();
        $orderCount   = Order::count();
        $revenue      = Order::where('status', 'completed')->sum('total_price');

        $latestOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'userCount', 'sellerCount', 'productCount', 'pendingProducts',
            'orderCount', 'revenue', 'latestOrders'
        ));
    }
}
