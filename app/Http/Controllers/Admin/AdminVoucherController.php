<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;

class AdminVoucherController extends Controller
{
    /**
     * Hiển thị danh sách voucher toàn hệ thống (shop_id = NULL)
     */
    public function index()
    {
        $vouchers = Voucher::whereNull('shop_id')->latest()->get();
        return view('admin.vouchers', compact('vouchers'));
    }

    /**
     * Hiển thị form thêm voucher mới
     */
    public function create()
    {
        return view('admin.voucher_create');
    }

    /**
     * Lưu voucher toàn hệ thống
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,code',
            'discount_amount' => 'required|numeric|min:1',
            'expiry_date' => 'required|date'
        ]);

        Voucher::create([
            'shop_id' => null, // ✅ Voucher của admin
            'code' => $request->code,
            'discount_amount' => $request->discount_amount,
            'expiry_date' => $request->expiry_date,
            'status' => 'active',
        ]);

        return redirect()->route('admin.vouchers.index')
            ->with('success', '🎉 Thêm voucher toàn hệ thống thành công!');
    }

    /**
     * Hiển thị form sửa voucher
     */
    public function edit($id)
    {
        $voucher = Voucher::whereNull('shop_id')->findOrFail($id);
        return view('admin.voucher_create', compact('voucher'));
    }

    /**
     * Cập nhật voucher
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::whereNull('shop_id')->findOrFail($id);

        $request->validate([
            'code' => 'required|unique:vouchers,code,' . $voucher->id,
            'discount_amount' => 'required|numeric|min:1',
            'expiry_date' => 'required|date'
        ]);

        $voucher->update([
            'code' => $request->code,
            'discount_amount' => $request->discount_amount,
            'expiry_date' => $request->expiry_date,
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.vouchers.index')
            ->with('success', '✏️ Cập nhật voucher thành công!');
    }

    /**
     * Xóa voucher toàn hệ thống
     */
    public function destroy($id)
    {
        Voucher::whereNull('shop_id')->where('id', $id)->delete();
        return back()->with('success', '🗑️ Đã xóa voucher toàn hệ thống!');
    }
}
