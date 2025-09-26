<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('account_personal');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:4096',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? $user->phone;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->save();

        // Trả về JSON để client xử lý reload
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công',
            'name' => $user->name,
            'avatar' => $user->avatar_path ? asset('storage/' . $user->avatar_path) : null,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => 'Mật khẩu hiện tại không đúng'],
            ], 422);
        }

        $user->password = $request->password; // hashed by cast
        $user->save();

        // Trả về JSON để client xử lý reload
        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công',
        ]);
    }

    public function listAddresses()
    {
        $addresses = Auth::user()->addresses()->latest()->get();
        return response()->json(['success' => true, 'addresses' => $addresses]);
    }

    public function addAddress(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:255',
            'ward' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            // Không validate boolean trực tiếp để tránh giá trị 'on' bị fail
        ]);

        $data['user_id'] = Auth::id();
        $isDefault = $request->boolean('is_default');
        $data['is_default'] = $isDefault;
        if ($isDefault) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }
        $addr = Address::create($data);
        return response()->json(['success' => true, 'address' => $addr]);
    }

    public function setDefaultAddress($id)
    {
        $addr = Address::where('user_id', Auth::id())->findOrFail($id);
        Address::where('user_id', Auth::id())->update(['is_default' => false]);
        $addr->is_default = true;
        $addr->save();
        return response()->json(['success' => true]);
    }

    public function deleteAddress($id)
    {
        $addr = Address::where('user_id', Auth::id())->findOrFail($id);
        $addr->delete();
        return response()->json(['success' => true]);
    }
}