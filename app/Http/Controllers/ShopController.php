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
    /**
     * Hiển thị form tạo shop (khi đăng ký seller)
     */
    public function create(Request $request)
    {
        // Nếu đang trong quá trình đăng ký seller (chưa có user trong DB)
        if ($request->session()->has('pending_seller')) {
            return view('shop_create');
        }

        // Nếu đã có user đăng nhập thì kiểm tra role
        $user = Auth::user();
        if ($user && $user->role === 'seller') {
            $existing = Shop::where('user_id', $user->id)->first();
            if ($existing) {
                return redirect()->route('seller.dashboard')
                    ->with('success', 'Bạn đã tạo shop rồi.');
            }
            return view('shop_create');
        }

        // Không hợp lệ → chặn
        abort(403);
    }

    /**
     * Xử lý lưu shop mới (khi đăng ký người bán)
     */
    public function store(Request $request)
    {
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

        // ✅ Tạo user mới
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
            'role' => 'seller',
            'status' => 'active',
        ]);

        // ✅ Tạo shop chờ duyệt
        Shop::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'description' => $validated['description'] ?? null,
            'registered_at' => now(),
            'status' => 'pending', // Chờ duyệt
        ]);

        session()->forget('pending_seller');
        Auth::login($user);

        return redirect()->route('seller.dashboard')
            ->with('success', 'Tạo shop thành công! Shop của bạn đang chờ duyệt.');
    }

    /**
     * Cập nhật thông tin shop từ tài khoản seller
     */
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

        // 🚫 Nếu shop đang chờ duyệt → không cho chỉnh sửa
        if ($shop->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => '🕒 Shop của bạn đang chờ duyệt, không thể chỉnh sửa lúc này.',
            ], 403);
        }

        // 🚫 Nếu shop bị đình chỉ → không cho chỉnh sửa
        if ($shop->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => '🚫 Shop của bạn đang bị tạm khóa. Không thể cập nhật thông tin.',
            ], 403);
        }

        // ✅ Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:4096',
        ]);

        // ✅ Cập nhật các trường hợp lệ
        $shop->name = $validated['name'];
        $shop->description = $validated['description'] ?? $shop->description;

        if ($request->hasFile('logo')) {
            $shop->logo_path = $request->file('logo')->store('shops', 'public');
        }

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

    /**
     * Hiển thị trang "Shop bị từ chối"
     */
    public function showRejected()
    {
        $shop = auth()->user()->shop;

        if (!$shop) {
            return redirect()->route('shops.create');
        }

        return view('shop_rejected', compact('shop'));
    }

    /**
     * Người bán gửi lại yêu cầu duyệt shop sau khi bị từ chối
     */
    public function resubmit(Request $request)
    {
        $shop = auth()->user()->shop;

        if (!$shop) {
            return redirect()->route('seller.dashboard')->with('error', 'Không tìm thấy shop.');
        }

        // 🚫 Nếu shop đang active hoặc pending thì không cho gửi lại
        if (in_array($shop->status, ['active', 'pending'])) {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Shop của bạn đang hoạt động hoặc đang chờ duyệt, không thể gửi lại.');
        }

        // ✅ Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ✅ Cập nhật lại thông tin shop và chuyển sang pending
        $shop->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        // ✅ Upload logo nếu có
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('shop_logos', 'public');
            $shop->logo_path = $path;
            $shop->save();
        }

        // ✅ Đăng xuất user (để đợi duyệt)
        auth()->logout();

        return redirect()->route('login')
            ->with('success', '✅ Đã gửi lại yêu cầu thành công! Vui lòng đăng nhập lại sau khi shop được duyệt.');
    }
    public function redirectDashboard()
{
    $user = Auth::user();

    if (!$user || $user->role !== 'seller') {
        abort(403);
    }

    $shop = Shop::where('user_id', $user->id)->first();

    if (!$shop) {
        return redirect()->route('shops.create')
            ->with('error', 'Bạn chưa tạo shop nào.');
    }

    if ($shop->status === 'pending') {
        return view('seller_dashboard', compact('shop')); // hiển thị giao diện chờ duyệt
    }

    if ($shop->status === 'rejected') {
        return redirect()->route('seller.shop.rejected');
    }

    if ($shop->status === 'suspended') {
        return view('seller_dashboard', compact('shop'));
    }

    // ✅ Nếu shop active, render dashboard bình thường
    return view('seller_dashboard', compact('shop'));
    }

}
