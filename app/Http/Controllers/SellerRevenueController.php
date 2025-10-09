<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Shop;

class SellerRevenueController extends Controller
{
    /**
     * Hiển thị thống kê doanh thu của người bán
     */
    public function index(Request $request)
    {
        $year = $request->query('year', now()->year);
        $sellerId = auth()->id();

        // 🔍 Lấy thông tin shop của người bán
        $shop = Shop::where('user_id', $sellerId)->first();

        if (!$shop) {
            return view('seller_dashboard', [
                'shop' => null,
                'revenues' => array_fill(0, 12, 0),
                'year' => $year,
                'totalRevenue' => 0,
                'totalOrders' => 0,
                'soldCount' => 0,
            ]);
        }

        // 🧾 Lấy tất cả đơn hàng hoàn thành của seller
        $orders = Order::whereHas('items', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->whereYear('created_at', $year)
            ->where('status', 'completed')
            ->with(['items' => function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            }])
            ->get();

        // 📊 Tổng hợp dữ liệu
        $totalOrders = $orders->count();
        $soldCount = $orders->flatMap(fn($o) => $o->items ?? collect())->sum('quantity');
        $totalRevenue = $orders->sum('total_price');

        // 💰 Doanh thu theo từng tháng
        $revenues = [];
        for ($m = 1; $m <= 12; $m++) {
            $revenues[$m] = Order::whereHas('items', function ($q) use ($sellerId) {
                    $q->where('seller_id', $sellerId);
                })
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->where('status', 'completed')
                ->sum('total_price');
        }

        // ✅ Trả dữ liệu về view seller_dashboard
        return view('seller_dashboard', [
            'shop' => $shop,
            'revenues' => $revenues,
            'year' => $year,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'soldCount' => $soldCount,
        ]);
    }
    public function getJson(Request $request)
{
    $sellerId = auth()->id();
    $year = $request->query('year', now()->year);
    $currentMonth = ($year == now()->year) ? now()->month : 12;

    $revenues = [];
    for ($m = 1; $m <= $currentMonth; $m++) {
        $revenues[$m] = \App\Models\Order::whereHas('items', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereYear('created_at', $year)
          ->whereMonth('created_at', $m)
          ->where('status', 'completed')
          ->sum('total_price');
    }

    return response()->json([
        'year' => $year,
        'revenues' => array_values($revenues),
    ]);
}

}
