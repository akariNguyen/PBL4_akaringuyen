<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        return view('home', ['mode' => 'login']);
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ], [
        'email.required' => 'Email là bắt buộc',
        'email.email' => 'Email không hợp lệ',
        'password.required' => 'Mật khẩu là bắt buộc',
        'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput($request->except('password'));
    }

    $credentials = $request->only('email', 'password');

    // kiểm tra email có tồn tại không
    $user = User::where('email', $request->email)->first();
    if ($user && $user->status === 'inactive') {
        return redirect()->back()
            ->withErrors(['email' => 'Tài khoản của bạn đã bị cấm sử dụng'])
            ->withInput($request->except('password'));
    }

    // Thử đăng nhập
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'seller') {
            return redirect()->route('seller.dashboard');
        } else {
            return redirect()->route('customer.dashboard');
        }
    }

    return redirect()->back()
        ->withErrors(['email' => 'Email hoặc mật khẩu không đúng'])
        ->withInput($request->except('password'));
}

    public function showRegisterForm()
    {
        return view('home', ['mode' => 'register']);
    }

    /**
     * Xử lý đăng ký
     */
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:customer,seller',
            'gender' => 'required|in:male,female',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Nếu là customer → tạo user ngay
        if ($request->role === 'customer') {
            $defaultAvatar = $request->gender === 'female'
                ? '/Picture/avata_macdinh_nu.png'
                : '/Picture/avata_macdinh_nam.png';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'avatar_path' => $defaultAvatar,
                'role' => 'customer',
                'status' => 'active',
            ]);

            Auth::login($user);

            return redirect()->route('customer.dashboard')
                ->with('success', 'Đăng ký thành công!');
        }

        // Nếu là seller → chưa tạo user, chỉ lưu thông tin vào session
        $request->session()->put('pending_seller', $request->only([
            'name', 'email', 'phone', 'password', 'gender'
        ]));

        return redirect()->route('shops.create')
            ->with('success', 'Hãy tạo shop để hoàn tất đăng ký Người bán.');
    }


    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'Đăng xuất thành công!');
    }
}
