<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Creating new order', [
            'user_id' => Auth::id(),
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity')
        ]);

        $user = Auth::user();
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = $validated['quantity'];

        $total = $product->price * $qty;
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $product->seller_id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => $qty,
        ]);

        \Log::info('Order created successfully', [
            'order_id' => $order->id,
            'seller_id' => $product->seller_id
        ]);

        return back()->with('success', 'Đã đặt hàng thành công!');
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->latest()
            ->get();

        \Log::info('Fetching user orders', [
            'user_id' => Auth::id(),
            'order_count' => $orders->count()
        ]);

        return view('orders_my', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        \Log::info('Attempting to update order status', [
            'order_id' => $id,
            'seller_id' => Auth::id(),
            'requested_status' => $request->input('status'),
            'ip' => $request->ip()
        ]);

        $order = Order::find($id);
        if (!$order) {
            \Log::error('Order not found', ['order_id' => $id]);
            return response()->json(['success' => false, 'message' => 'Đơn hàng không tồn tại'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,shipped,completed,cancelled',
        ]);

        $hasAccess = OrderItem::where('order_id', $id)
            ->where('seller_id', Auth::id())
            ->exists();

        if (!$hasAccess) {
            \Log::error('Access denied for updating order', [
                'order_id' => $id,
                'seller_id' => Auth::id()
            ]);
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật đơn hàng này'], 403);
        }

        $order->status = $validated['status'];
        $order->save();

        \Log::info('Order status updated', [
            'order_id' => $id,
            'new_status' => $validated['status']
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
    }

    public function cancelByCustomer(Request $request, $id)
    {
        \Log::info('Customer requests cancel order', [
            'order_id' => $id,
            'user_id' => Auth::id(),
        ]);

        $order = Order::with('items')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng không hợp lệ'], 404);
        }
        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể hủy đơn ở trạng thái Chờ xử lý'], 422);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['success' => true, 'message' => 'Đã hủy đơn hàng']);
    }
}