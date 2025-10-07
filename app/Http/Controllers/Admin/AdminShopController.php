<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class AdminShopController extends Controller
{
    public function index(Request $request)
{
    $shopCount = \App\Models\Shop::count();

    $defaultFrom = \App\Models\Shop::min('created_at');
    $defaultFrom = $defaultFrom ? \Carbon\Carbon::parse($defaultFrom)->format('Y-m-d') : now()->format('Y-m-d');
    $defaultTo = now()->format('Y-m-d');

    $search = $request->input('search');
    $from = $request->input('from', $defaultFrom);
    $to = $request->input('to', $defaultTo);

    if ($from > $to) {
        return redirect()->back()->with('error', 'Ngày bắt đầu không được lớn hơn ngày kết thúc');
    }

    $sortBy = $request->input('sort_by', 'created_at');
    $sortOrder = $request->input('sort_order', 'desc');

    $shops = \App\Models\Shop::query()
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%$search%");
        })
        ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
        ->orderBy($sortBy, $sortOrder)
        ->get();

    return view('admin.shops', compact('shops', 'shopCount', 'defaultFrom', 'defaultTo'));
}


    public function show($id)
    {
        $shop = Shop::with('owner')->findOrFail($id); 
        return view('admin.shop_show', compact('shop'));
    }

    public function toggleStatus($id)
    {
        $shop = Shop::findOrFail($id);

        // Nếu đang active -> chuyển sang suspended
        if ($shop->status === 'active') {
            $shop->status = 'suspended';
        } 
        // Nếu đang suspended -> chuyển sang active
        elseif ($shop->status === 'suspended') {
            $shop->status = 'active';
        }

        $shop->save();

        return back()->with('success', 'Đã thay đổi tình trạng shop!');
    }
    public function pending()
{
    $shops = \App\Models\Shop::where('status', 'pending')->get();
    return view('admin.shops_pending', compact('shops'));
}

public function approve($id)
{
    $shop = \App\Models\Shop::findOrFail($id);
    $shop->status = 'active';
    $shop->save();
    return back()->with('success', 'Shop đã được duyệt!');
}

public function reject($id)
{
    $shop = \App\Models\Shop::findOrFail($id);
    $shop->status = 'rejected';
    $shop->save();
    return back()->with('success', 'Shop đã bị từ chối!');
}
    public function showDetail($id)
{
    $shop = \App\Models\Shop::with('user')->findOrFail($id);

    // Số sản phẩm đang bán (status = in_stock)
    $inStockCount = \App\Models\Product::where('seller_id', $shop->user_id)
        ->where('status', 'in_stock')
        ->count();

    // Tổng sản phẩm đã bán
    $soldCount = \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->where('status', 'completed');
        })
        ->whereHas('product', function ($q) use ($shop) {
            $q->where('seller_id', $shop->user_id);
        })
        ->sum('quantity');

    // Tổng doanh thu
    $totalRevenue = \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->where('status', 'completed');
        })
        ->whereHas('product', function ($q) use ($shop) {
            $q->where('seller_id', $shop->user_id);
        })
        ->selectRaw('SUM(quantity * price) as revenue')
        ->value('revenue');

    return response()->json([
        'shop' => $shop,
        'seller' => $shop->user,
        'inStockCount' => $inStockCount,
        'soldCount' => $soldCount,
        'totalRevenue' => number_format($totalRevenue ?? 0, 0, ',', '.') . ' ₫',
    ]);
}

}
