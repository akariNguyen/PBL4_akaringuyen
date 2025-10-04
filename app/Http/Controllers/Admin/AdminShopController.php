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
        $shop->status = $shop->status === 'active' ? 'inactive' : 'active';
        $shop->save();

        return back()->with('success', 'Đã thay đổi tình trạng shop!');
    }
}
