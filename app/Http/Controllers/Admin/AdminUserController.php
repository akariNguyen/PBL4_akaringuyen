<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
{
    // --- Thống kê ---
    $userCount = \App\Models\User::count();

    // --- Default ngày lọc ---
    $defaultFrom = \App\Models\User::min('created_at');
    $defaultFrom = $defaultFrom ? \Carbon\Carbon::parse($defaultFrom)->format('Y-m-d') : now()->format('Y-m-d');
    $defaultTo = now()->format('Y-m-d');

    // --- Lấy dữ liệu filter ---
    $search = $request->input('search');
    $from = $request->input('from', $defaultFrom);
    $to = $request->input('to', $defaultTo);

    // Validate nếu from > to
    if ($from > $to) {
        return redirect()->back()->withErrors(['date' => 'Ngày bắt đầu không được lớn hơn ngày kết thúc']);
    }

    $sortBy = $request->input('sort_by', 'created_at');
    $sortOrder = $request->input('sort_order', 'desc');

    // --- Query user ---
    $users = \App\Models\User::query()
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%");
        })
        ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
        ->orderBy($sortBy, $sortOrder)
        ->get();

    return view('admin.users', compact('users', 'userCount', 'defaultFrom', 'defaultTo'));
}





    public function show($id)
    {
        $user = User::with(['shop', 'reviews'])->findOrFail($id);
        // nếu muốn có file riêng thì tạo admin.user_show.blade.php
        return view('admin.user_show', compact('user'));
    }

    public function create()
    {
        return view('admin.user_create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role'  => 'required|in:admin,seller,customer',
            'password' => 'required|min:6'
        ]);

        $data['password'] = bcrypt($data['password']);
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Tạo user thành công!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user_edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,seller,customer',
            'status' => 'nullable|string',
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật user thành công!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Xóa user thành công!');
    }
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'Đã thay đổi tình trạng tài khoản!');
    }

}
