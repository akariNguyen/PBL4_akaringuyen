<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    public function create(Request $request)
    {
        // Nếu đang trong quá trình đăng ký seller (chưa có user trong DB)
        if ($request->session()->has('pending_seller')) {
            return view('shop_create');
        }

        // Nếu đã có user đăng nhập thì kiểm tra role
        $user = Auth::user();
        if ($user && $user->role === 'seller') {
            $existing = Shop::find($user->id);
            if ($existing) {
                return redirect()->route('seller.dashboard')->with('success', 'Bạn đã tạo shop rồi.');
            }
            return view('shop_create');
        }

        // Không hợp lệ → chặn
        abort(403);
    }


    public function store(Request $request)
    {
        // Lấy thông tin pending seller từ session
        $pendingSeller = session('pending_seller');

        if (!$pendingSeller) {
            return redirect()->route('register')
                ->withErrors(['register' => 'Bạn cần đăng ký trước khi tạo shop.']);
        }

        // Validate shop
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
           
            'logo' => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('shops', 'public');
        }

        // Tạo user (lúc này mới lưu vào DB)
        $defaultAvatar = $pendingSeller['gender'] === 'female'
           ? '/Picture/Avata/avatar_macdinh_nu.jpg'
            : '/Picture/Avata/avatar_macdinh_nam.jpg';

        $user = User::create([
            'name' => $pendingSeller['name'],
            'email' => $pendingSeller['email'],
            'phone' => $pendingSeller['phone'],
            'password' => Hash::make($pendingSeller['password']),
            'gender' => $pendingSeller['gender'],
            'avatar_path' => $defaultAvatar,
            'role' => 'seller', // ✅ Lưu seller luôn vì đã có shop
            'status' => 'active',
        ]);

        // Tạo shop gắn với user
        Shop::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'description' => $validated['description'] ?? null,
            'registered_at' => now(),
            'status' => 'pending', // Chờ duyệt
        ]);

        // Xóa session pending seller
        session()->forget('pending_seller');

        // Đăng nhập
        Auth::login($user);

        return redirect()->route('seller.dashboard')
            ->with('success', 'Tạo shop thành công! Bạn đã trở thành Người bán.');
    }


    public function updateAccount(Request $request)
{
    $user = Auth::user();
    if (!$user || $user->role !== 'seller') {
        abort(403);
    }

    $shop = Shop::where('user_id', $user->id)->first();
    if (!$shop) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy shop để cập nhật.',
        ], 404);
    }

    // 🚫 Nếu shop bị treo (suspended) → không cho phép chỉnh sửa
    if ($shop->status === 'suspended') {
        return response()->json([
            'success' => false,
            'message' => '🚫 Shop của bạn đang bị tạm khóa. Không thể cập nhật thông tin.',
        ], 403);
    }

    // ✅ Validate, KHÔNG bao gồm "status"
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'logo' => 'nullable|image|max:4096',
    ]);

    // ✅ Cập nhật các trường được phép
    $shop->name = $validated['name'];
    $shop->description = $validated['description'] ?? $shop->description;

    if ($request->hasFile('logo')) {
        $shop->logo_path = $request->file('logo')->store('shops', 'public');
    }

    // 🚫 KHÔNG thay đổi trạng thái
    $shop->save();

    return response()->json([
        'success' => true,
        'message' => '✅ Cập nhật thông tin shop thành công!',
        'name' => $shop->name,
        'logo' => $shop->logo_path
            ? Storage::disk('public')->url($shop->logo_path)
            : '/Picture/logo.png',
    ]);
}



    public function showRejected()
{
    $shop = auth()->user()->shop;

    if (!$shop) {
        return redirect()->route('shops.create');
    }

    return view('shop_rejected', compact('shop'));  // ✅ Fix: Đổi thành 'shop_rejected' để khớp file
}

public function resubmit(Request $request)
{
    $shop = auth()->user()->shop;

    // --- Validate dữ liệu ---
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // --- Cập nhật thông tin shop ---
    $shop->update([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'status' => 'pending', // Đặt lại trạng thái chờ duyệt
    ]);

    // --- Upload logo (nếu có) ---
    if ($request->hasFile('logo')) {
        $path = $request->file('logo')->store('shop_logos', 'public');
        $shop->logo_path = $path;
        $shop->save();
    }

    // --- Đăng xuất người dùng ---
    auth()->logout();   

    // --- Quay về trang đăng nhập kèm thông báo ---
    return redirect()->route('login')->with('success', '✅ Đã gửi yêu cầu thành công! Vui lòng đăng nhập lại sau khi shop được duyệt.');
}


}