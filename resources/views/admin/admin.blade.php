<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Market - Admin</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root { --border:#e5e7eb; --muted:#6b7280; }
    body { margin:0; font-family: Inter, sans-serif; background:#fff; color:#111827; }
    .topbar { height:56px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; padding:0 16px; background:#fff; position:sticky; top:0; z-index:50; }
    .brand { display:flex; align-items:center; gap:10px; font-weight:700; }
    .brand img { height:40px; }
    .layout { display:grid; grid-template-columns: 240px 1fr; min-height: calc(100vh - 56px); }
    .sidebar { border-right:1px solid var(--border); padding:16px; background:#fff; }
    .side-title { font-weight:700; margin-bottom:8px; font-size:14px; }
    .menu { list-style:none; margin:0; padding:0; }
    .menu li { margin:8px 0; }
    .menu a { display:block; padding:8px 10px; text-decoration:none; color:#111827; border-radius:8px; cursor:pointer; }
    .menu a:hover { background:#f3f4f6; }
    .content { background:#f9fafb; padding:16px; }
    .card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:16px; margin-bottom:16px; }
    .logout-btn { padding:10px 20px; border:1px solid #d1d5db; border-radius:8px; background:#fff; color:#111827; font-size:18px; font-weight:600; text-decoration:none; }
    .logout-btn:hover { background:#f3f4f6; }

    /* Submenu style */
    .menu .submenu {
      display: none;
      list-style: none;
      padding-left: 16px;
      margin: 4px 0;
    }
    .menu .submenu li a {
      font-size: 14px;
      color: #374151;
      padding: 6px 12px;
      display: block;
      border-radius: 6px;
    }
    .menu .submenu li a:hover {
      background: #f3f4f6;
    }
  </style>

  {{-- CSS riêng cho từng view con --}}
  @stack('styles')
</head>
<body>
  <!-- Header -->
  <div class="topbar">
    <div class="brand">
      <img src="/Picture/logo.png" alt="E-Market">
      <span style="font-size:20px; color:#2563eb;">E-Market</span>
      <span style="font-size:20px; color:#ef4444;">Admin</span>
    </div>
    <div>
      <a href="#" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Đăng xuất
      </a>
      <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">
        @csrf
      </form>
    </div>
  </div>

  <!-- Layout -->
  <div class="layout">
    <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="side-section">
        <div class="side-title">Quản trị hệ thống</div>
        <ul class="menu">
          <!-- Quản lý người dùng -->
          <li><a href="{{ route('admin.users.index') }}">👥 Quản lý người dùng</a></li>

          <!-- Quản lý shop có submenu -->
          <li class="has-submenu">
            <a href="#">🏬 Quản lý shop ▾</a>
            <ul class="submenu">
              <li><a href="{{ route('admin.shops.index') }}">📋 Thông tin shop</a></li>
              <li><a href="{{ route('admin.shops.pending') }}">⏳ Duyệt tạo shop</a></li>
            </ul>
          </li>

          <!-- Quản lý sản phẩm có submenu -->
          <li class="has-submenu">
            <a href="#">📦 Quản lý sản phẩm ▾</a>
            <ul class="submenu">
              <li><a href="{{ route('admin.products.inStock') }}">📋 Danh sách sản phẩm</a></li>
              <li><a href="{{ route('admin.products.pending') }}">⏳ Duyệt sản phẩm</a></li>
            </ul>
          </li>

          <!-- Quản lý đơn hàng -->
          <li><a href="{{ route('admin.orders.index') }}">📑 Quản lý đơn hàng</a></li>

          <!-- Quản lý voucher -->
          <li class="has-submenu">
            <a href="#">🎟️ Quản lý voucher ▾</a>
            <ul class="submenu">
              <li><a href="{{ route('admin.vouchers.index') }}">📋 Danh sách voucher</a></li>
              <li><a href="{{ route('admin.vouchers.create') }}">➕ Thêm voucher mới</a></li>
            </ul>
          </li>


          <!-- ✅ Thống kê có submenu -->
          <li class="has-submenu">
            <a href="#">📊 Thống kê ▾</a>
            <ul class="submenu">
              <li><a href="{{ route('admin.analytics') }}">📈 Phân tích doanh thu</a></li>
              {{-- Bạn có thể thêm các mục thống kê khác ở đây nếu muốn --}}
              {{-- <li><a href="#">📅 Thống kê theo tháng</a></li> --}}
              <li><a href="{{ route('admin.analytics.products') }}">📊 Phân tích sản phẩm</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </aside>


    <!-- Content -->
    <main class="content">
      @yield('content')
    </main>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Toggle submenu khi click
    document.querySelectorAll('.has-submenu > a').forEach(function(parentLink) {
      parentLink.addEventListener('click', function(e) {
        e.preventDefault();
        let submenu = this.nextElementSibling;
        if (submenu.style.display === "block") {
          submenu.style.display = "none";
        } else {
          submenu.style.display = "block";
        }
      });
    });
  </script>

  {{-- JS riêng cho từng view con --}}
  @stack('scripts')
</body>
</html>
