<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            abort(403);
        }

        $existing = Shop::find($user->id);
        if ($existing) {
            return redirect()->route('seller.dashboard')->with('success', 'Bạn đã tạo shop rồi.');
        }

        return view('shop_create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            abort(403);
        }

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

        Shop::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'description' => $validated['description'] ?? null,
            'registered_at' => now(),
            'status' => $validated['status'],
        ]);

        // Nếu user có ý định là seller thì cập nhật role sau khi tạo shop thành công
        if (session()->pull('intent_seller', false)) {
            $user->role = 'seller';
            $user->save();
        }

        return redirect()->route('seller.dashboard')->with('success', 'Tạo shop thành công! Tài khoản của bạn đã trở thành Người bán.');
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