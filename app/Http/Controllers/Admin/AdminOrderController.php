<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng với lọc, tìm kiếm, và trạng thái.
     */
    public function index(Request $request)
{
    $status = $request->input('status', 'pending');
    $allowed = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

    if (!in_array($status, $allowed)) {
        $status = 'pending';
    }

    // ✅ Lấy ngày cũ nhất & hiện tại
    $oldestOrder = Order::orderBy('created_at', 'asc')->first();
    $defaultFrom = $oldestOrder ? $oldestOrder->created_at->format('Y-m-d') : now()->subMonth()->format('Y-m-d');
    $defaultTo = now()->format('Y-m-d');

    // ✅ Gán giá trị bộ lọc ngày (nếu người dùng không chọn thì dùng mặc định)
    $from = $request->input('from', $defaultFrom);
    $to = $request->input('to', $defaultTo);

    // --- Query cơ bản ---
    $query = Order::with([
        'user',
        'items.product.seller.shop', // lấy luôn seller & shop
    ])->where('status', $status);

    // --- Bộ lọc ngày ---
    $query->whereDate('created_at', '>=', $from)
          ->whereDate('created_at', '<=', $to);

    // --- Tìm kiếm theo khách hàng hoặc tên shop ---
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($u) use ($search) {
                $u->where('name', 'like', "%$search%");
            })->orWhereHas('items.product.seller.shop', function ($s) use ($search) {
                $s->where('name', 'like', "%$search%");
            });
        });
    }

    // --- Sắp xếp từ đơn hàng cũ nhất đến mới nhất ---
    $orders = $query->orderBy('created_at', 'asc')->paginate(10);

    // ✅ Gửi biến mặc định ra view để hiển thị
    return view('admin.orders', compact('orders', 'status', 'defaultFrom', 'defaultTo'));
}


    /**
     * Hiển thị chi tiết đơn hàng.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'items.product.seller.shop'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }
}
