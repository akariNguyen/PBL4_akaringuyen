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
            'status' => 'required|in:active,suspended,closed',
            'logo' => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('shops', 'public');
        }

        // Tạo user (lúc này mới lưu vào DB)
        $defaultAvatar = $pendingSeller['gender'] === 'female'
            ? '/Picture/avata_macdinh_nu.png'
            : '/Picture/avata_macdinh_nam.png';

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
            'status' => $validated['status'],
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

        $shop = Shop::find($user->id);
        if (!$shop) {
            return redirect()->back()->withErrors(['shop' => 'Chưa có shop để cập nhật.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,suspended,closed',
            'logo' => 'nullable|image|max:4096',
        ]);

        $shop->name = $validated['name'];
        $shop->description = $validated['description'] ?? $shop->description;
        $shop->status = $validated['status'];
        if ($request->hasFile('logo')) {
            $shop->logo_path = $request->file('logo')->store('shops', 'public');
        }
        $shop->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật shop thành công', 'name' => $shop->name, 'logo' => $shop->logo_path ? Storage::disk('public')->url($shop->logo_path) : '/Picture/logo.png']);
    }
}