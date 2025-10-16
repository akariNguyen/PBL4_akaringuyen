<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Market</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#111827; --accent:#2563eb; }
        * { box-sizing:border-box; margin:0; padding:0; }
        html, body { height:100%; font-family:'Inter', sans-serif; }

        /* NAVBAR */
        .navbar {
            position:sticky; top:0; background:#ffffff;
            border-bottom:1px solid #e5e7eb; z-index:50;
            height:64px;
        }
        .nav-inner {
            max-width:1200px; margin:0 auto;
            display:flex; align-items:center; justify-content:space-between;
            height:100%; padding:0 24px;
        }
        .brand { display:flex; align-items:center; gap:10px; font-weight:700; font-size:24px; color:var(--primary); white-space:nowrap; }
        .brand img { height:80px; width:auto; display:block; }
        .nav-actions a {
            text-decoration:none; margin-left:12px;
            padding:10px 16px; border-radius:8px;
            border:1px solid #e5e7eb; color:#111827;
        }
        .nav-actions a.primary {
            background:#2563eb; color:#fff; border-color:#2563eb;
        }

        /* HERO */
        .hero {
            height:100vh;
            width:100%;
            display:flex; align-items:flex-start; justify-content:flex-start;
            position:relative;
            overflow:visible;
        }
        .hero img {
            position:absolute; inset:0;
            width:100%; height:100vh;
            object-fit:cover;
            z-index:-1;
            top:0;
        }
        .hero-content {
            background:rgba(255,255,255,0.85);
            padding:40px;
            border-radius:12px;
            max-width:500px;
            width:100%;
            text-align:center;
            box-shadow:0 4px 16px rgba(0,0,0,0.15);
            margin:120px 0 0 24px;
        }

        /* FORM */
        .field { margin-bottom:14px; text-align:left; }
        label { display:block; margin-bottom:6px; font-weight:600; font-size:14px; color:#374151; }
        input, select {
            width:100%; padding:12px 14px;
            border:1px solid #d1d5db; border-radius:8px;
            background:#fff;
        }
        .btn {
            width:100%; padding:12px 16px;
            background:#2563eb; color:#fff;
            border:none; border-radius:8px;
            cursor:pointer; font-weight:600;
        }
        .helper { margin-top:10px; font-size:14px; color:#6b7280; }

        /* ✅ Hàng ngang cho 2 ô */
        .field-row {
            display:flex;
            gap:16px;
            align-items:flex-start;
            margin-bottom:14px;
        }
        .field-row .field {
            margin-bottom:0;
        }

        /* ✅ Responsive: màn nhỏ sẽ tự xuống dòng */
        @media (max-width:600px) {
            .field-row { flex-direction:column; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="nav-inner">
            <div class="brand"><img src="{{ asset('Picture/Logo.png') }}" alt="Logo"> E-Market</div>
            <div class="nav-actions">
                <a href="/login">Đăng nhập</a>
                <a class="primary" href="/register">Đăng ký</a>
            </div>
        </div>
    </div>

    <!-- Hero -->
    <div class="hero">
        <img src="{{ asset('Picture/backgorund_shopping.jpg') }}" alt="Shopping">

        <div class="hero-content">
            @if($mode === 'welcome')
                <h1 style="margin-bottom:20px; font-size:32px;">Chào mừng bạn đến với E-Market</h1>
                <p style="color:#4b5563; line-height:1.8; font-size:18px;">
                    Khám phá hàng ngàn sản phẩm đa dạng từ thời trang, công nghệ đến nhu yếu phẩm.  
                    Mua sắm tiện lợi, giá cả minh bạch, thanh toán an toàn và giao hàng tận tay.
                </p>
            @elseif($mode === 'login')
                <h2 style="margin:0 0 16px 0; font-size:24px;">Đăng nhập</h2>
                
                @if ($errors->any())
                    <div style="background:#fee2e2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;">
                        <ul style="margin:0; padding-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('login') }}">
                    @csrf
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="field">
                        <label for="password">Mật khẩu</label>
                        <input id="password" type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button class="btn" type="submit">Đăng nhập</button>
                    <div class="helper">Chưa có tài khoản? <a href="/register">Đăng ký</a></div>
                </form>

            @elseif($mode === 'register')
                <h2 style="margin:0 0 16px 0; font-size:24px;">Đăng ký</h2>
                
                @if ($errors->any())
                    <div style="background:#fee2e2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px;">
                        <ul style="margin:0; padding-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('register') }}">
                    @csrf
                    <div class="field">
                        <label for="name">Họ và tên</label>
                        <input id="name" type="text" name="name" placeholder="Nguyễn Văn A" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label for="reg_email">Email</label>
                        <input id="reg_email" type="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required>
                    </div>

                    <!-- ✅ Số điện thoại + Giới tính (70/30) -->
                    <div class="field-row">
                        <div class="field" style="flex:7">
                            <label for="phone">Số điện thoại</label>
                            <input id="phone" type="tel" name="phone" placeholder="09xx xxx xxx" value="{{ old('phone') }}" required>
                        </div>
                        <div class="field" style="flex:3">
                            <label for="gender">Giới tính</label>
                            <select id="gender" name="gender" required>
                                <option value="male" {{ old('gender')=='male' ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ old('gender')=='female' ? 'selected' : '' }}>Nữ</option>
                            </select>
                        </div>
                    </div>

                    <!-- ✅ Mật khẩu + Nhập lại (50/50) -->
                    <div class="field-row">
                        <div class="field" style="flex:1">
                            <label for="reg_password">Mật khẩu</label>
                            <input id="reg_password" type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="field" style="flex:1">
                            <label for="password_confirmation">Nhập lại mật khẩu</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="role">Vai trò</label>
                        <select id="role" name="role" required>
                            <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Khách hàng</option>
                            <option value="seller" {{ old('role') == 'seller' ? 'selected' : '' }}>Người bán</option>
                        </select>
                    </div>

                    <button class="btn" type="submit">Tạo tài khoản</button>
                    <div class="helper">Đã có tài khoản? <a href="/login">Đăng nhập</a></div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
