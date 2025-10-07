<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * 🧾 Hiển thị danh sách voucher của shop (trang chính)
     */
    public function index()
{
    $shop = Shop::where('user_id', Auth::id())->first();
    if (!$shop) {
        return redirect()->back()->withErrors(['shop' => 'Không tìm thấy shop!']);
    }

    $vouchers = Voucher::where('shop_id', $shop->id)->latest()->get();

    // ✅ Sửa đúng view
    return view('seller_vouchers', compact('vouchers'));
}




    /**
     * ➕ Trang thêm voucher mới
     */
    public function create()
{
    return view('seller_vouchers_create');
}


    /**
     * 💾 Lưu voucher mới
     */
    public function store(Request $request)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy shop!']);
        }

        $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'discount_amount' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
        ]);

        $voucher = Voucher::create([
            'shop_id' => $shop->user_id,
            'code' => strtoupper($request->code),
            'discount_amount' => $request->discount_amount,
            'expiry_date' => $request->expiry_date,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm voucher mới thành công!',
            'voucher' => $voucher
        ]);
    }

    /**
     * ✏️ Cập nhật voucher (AJAX)
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $voucher->update([
            'discount_amount' => $request->discount_amount,
            'expiry_date' => $request->expiry_date,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
    }

    /**
     * 🗑️ Xóa voucher (AJAX)
     */
    public function destroy($id)
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher không tồn tại!']);
        }

        $voucher->delete();
        return response()->json(['success' => true, 'message' => 'Xóa voucher thành công!']);
    }
    public function listJson()
{
    $shop = Auth::user()->shop;
    $vouchers = \App\Models\Voucher::where('shop_id', $shop->user_id)
        ->latest()
        ->get();

    return response()->json($vouchers);
}


}
