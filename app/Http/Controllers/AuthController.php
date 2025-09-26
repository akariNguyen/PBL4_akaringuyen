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

        // Thử đăng nhập
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Chuyển hướng dựa trên role
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

    /**
     * Hiển thị form đăng ký
     */
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
        ], [
            'name.required' => 'Họ tên là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email này đã được sử dụng',
            'phone.required' => 'Số điện thoại là bắt buộc',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'role.required' => 'Vai trò là bắt buộc',
            'role.in' => 'Vai trò không hợp lệ',
            'gender.required' => 'Giới tính là bắt buộc',
            'gender.in' => 'Giới tính không hợp lệ',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        // Tạo user mới (chưa gán role seller cho tới khi tạo shop thành công)
        $initialRole = $request->role === 'seller' ? 'customer' : 'customer';
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
            'role' => $initialRole,
            'status' => 'active',
        ]);

        // Đăng nhập tự động sau khi đăng ký
        Auth::login($user);

        // Nếu người dùng chọn vai trò người bán, dẫn tới tạo shop và đánh dấu ý định trong session
        if ($request->role === 'seller') {
            $request->session()->put('intent_seller', true);
            return redirect()->route('shops.create')->with('success', 'Đăng ký thành công! Hãy tạo thông tin shop của bạn.');
        }

        return redirect()->route('customer.dashboard')->with('success', 'Đăng ký thành công!');
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
