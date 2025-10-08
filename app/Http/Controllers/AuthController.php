<?php

    namespace App\Http\Controllers;

    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\Log; // ✅ chỉ ở đây (ngoài class)

    class AuthController extends Controller
    {
        public function showLoginForm()
        {
            return view('home', ['mode' => 'login']);
        }

        // ❌ Đừng đặt "use Illuminate\Support\Facades\Log;" ở đây!

        public function login(Request $request)
        {
            Log::info('🔹 Bắt đầu xử lý đăng nhập', ['email' => $request->email]);

            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required|min:6',
            ], [
                'email.required' => 'Email là bắt buộc',
                'email.email'    => 'Email không hợp lệ',
                'password.required' => 'Mật khẩu là bắt buộc',
                'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự',
            ]);

            if ($validator->fails()) {
                Log::warning('⚠️ Validation thất bại', ['errors' => $validator->errors()]);
                return back()->withErrors($validator)->withInput($request->except('password'));
            }

            $credentials = $request->only('email', 'password');

            $user = User::where('email', $request->email)->first();
            if ($user) {
                Log::info('🔍 Tìm thấy user', ['user_id' => $user->id, 'role' => $user->role, 'status' => $user->status]);
                if ($user->status === 'inactive') {
                    Log::warning('🚫 Tài khoản bị cấm sử dụng', ['email' => $request->email]);
                    return back()->withErrors(['email' => 'Tài khoản của bạn đã bị cấm sử dụng'])
                                ->withInput($request->except('password'));
                }
            } else {
                Log::warning('❌ Không tìm thấy user theo email', ['email' => $request->email]);
            }

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                $user = Auth::user();

                Log::info('✅ Đăng nhập thành công', ['user_id' => $user->id, 'role' => $user->role]);

                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'seller') {
                    $shop = $user->shop;

                    if ($shop) {
                        Log::info('🛍️ Kiểm tra shop', ['shop_id' => $shop->user_id, 'status' => $shop->status]);
                        if ($shop->status === 'rejected') {
                            Log::warning('⚠️ Shop bị từ chối — chuyển hướng đến trang chỉnh sửa');
                            return redirect()->route('seller.shop.rejected');
                        }
                    } else {
                        Log::info('🆕 Seller chưa có shop — chuyển hướng đến tạo shop');
                        return redirect()->route('shops.create');
                    }

                    return redirect()->route('seller.dashboard');
                } else {
                    return redirect()->route('customer.dashboard');
                }
            }

            Log::error('❌ Đăng nhập thất bại', ['email' => $request->email]);
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng'])
                        ->withInput($request->except('password'));
        }

        // ... các method khác giữ nguyên




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
