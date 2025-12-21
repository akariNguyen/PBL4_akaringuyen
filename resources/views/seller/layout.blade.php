<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kênh Người Bán - E‑Market</title>
    <style>
        :root { --primary:#111827; --muted:#6b7280; --border:#e5e7eb; --bg:#f8fafc; --brand:#ef4444; }
        * { box-sizing:border-box; }
        body { margin:0; font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#fff; color:#111827; }
        .topbar { height:56px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:flex-start; padding:0 16px; position:sticky; top:0; background:#fff; z-index:50; }
        .left { display:flex; align-items:center; gap:12px; }
        .brand { display:flex; align-items:center; gap:10px; font-weight:700; white-space:nowrap; }
        .brand img { height:80px; width:auto; }
        .brand span.e-market { font-size: 28px; color: #2563eb; }
        .brand span.channel { font-size: 28px; color: #ef4444; }
        .layout { display:grid; grid-template-columns: 240px 1fr; min-height: calc(100vh - 56px); }
        .sidebar { border-right:1px solid var(--border); padding:16px; background:#fff; }
        .side-title { font-weight:700; margin-bottom:8px; }
        .menu { list-style:none; padding:0; margin:0; }
        .menu li { margin:8px 0; }
        .menu a { text-decoration:none; color:#111827; padding:8px 10px; display:block; border-radius:8px; }
        .menu a:hover { background:#f3f4f6; }
        .content { background:#fafafa; padding:16px; }
        .card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:16px; }
        .grid { display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; }
        .metric { text-align:center; padding:16px; border:1px solid var(--border); border-radius:12px; background:#fff; }
        .metric h3 { margin:0 0 6px 0; font-size:14px; color:var(--muted); font-weight:600; }
        .metric .val { font-size:28px; font-weight:700; }
        .tabs { display:flex; gap:0; border-bottom:1px solid #e5e7eb; margin-bottom:12px; }
        .tab { padding:10px 14px; cursor:pointer; border:0; background:none; color:#6b7280; font-weight:600; }
        .tab.active { color:#111827; position:relative; }
        .tab.active::after { content:""; position:absolute; left:10px; right:10px; bottom:-1px; height:2px; background:#2563eb; }
        .section { padding-top:8px; }
        .row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
        .label { color:#6b7280; min-width:140px; }
        input[type="text"], input[type="password"], textarea, select { padding:10px 12px; border:1px solid #d1d5db; border-radius:8px; }
        .edit-input { max-width: 250px; }
        .btn { padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; cursor:pointer; font-size:14px; }
        .btn.primary { background:#2563eb; color:#fff; border-color:#2563eb; }
        .btn.green { background:#16a34a; color:#fff; border-color:#16a34a; }
        .btn.green:hover { background:#15803d; border-color:#15803d; }
        .btn.orange { background:#f59e0b; color:#fff; border-color:#f59e0b; }
        .btn.orange:hover { background:#d97706; border-color:#d97706; }
        .btn.red { background:#dc2626; color:#fff; border-color:#dc2626; }
        .btn.red:hover { background:#b91c1c; border-color:#b91c1c; }
        .btn:disabled { background:#d1d5db; color:#6b7280; cursor:not-allowed; }
        .icon-btn { border:none; background:none; cursor:pointer; color:#2e7d32; padding:10px; font-size:18px; border-radius:8px; }
        .icon-btn:hover { background:#f3f4f6; color:#1b5e20; }
        .muted { color:#6b7280; }
        .avatar { height:48px; width:48px; border-radius:999px; object-fit:cover; }
        .logo { height:56px; width:56px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; }
        .actions { display:flex; gap:8px; }
        .hint { color:#6b7280; font-size:13px; }
        .inline { flex:1; }
        .view-text { display:inline; }
        .edit-input { display:none; }
        .editing .view-text { display:none; }
        .editing .edit-input { display:inline-block; }
        .save-bar { display:none; justify-content:flex-start; gap:8px; margin-top:8px; }
        .editing .save-bar { display:flex; }
        .success-message { background:#ecfdf5; color:#047857; padding:10px 12px; border:1px solid #a7f3d0; border-radius:8px; margin-bottom:12px; }
        .suspended-alert { background:#fee2e2; color:#dc2626; padding:16px; border:1px solid #fecaca; border-radius:8px; margin-bottom:16px; }
        table { width:100%; border-collapse:separate; border-spacing:0 8px; }
        th, td { padding:12px; text-align:left; }
        th { background:#f3f4f6; font-weight:600; }
        tr { background:#fff; border:1px solid #e5e7eb; border-radius:8px; }
        @media (max-width: 1024px) { .grid { grid-template-columns: repeat(2, 1fr); } .layout { grid-template-columns: 200px 1fr; } }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } .layout { grid-template-columns: 1fr; } .sidebar { display:none; } }
    </style>
</head>
<body>
@php
    $shop = \App\Models\Shop::where('user_id', auth()->id())->first();
@endphp

<div class="topbar">
    <div class="left">
        <div class="brand" style="display:flex; align-items:center; gap:10px;">
            <img src="{{ asset('Picture/Logo.png') }}" alt="E-Market" style="height:80px; width:auto; display:block;">
            <span class="e-market" style="color:#2563eb; font-weight:700;">E-Market</span>
            <span class="channel" style="color:#6b7280; font-weight:500;">Kênh bán hàng</span>
        </div>
    </div>
</div>

<div class="layout">
    @if($shop && $shop->status === 'pending')
        <!-- 🚫 SHOP ĐANG CHỜ DUYỆT -->
        <div style="grid-column: 1 / -1; display:flex; flex-direction:column; align-items:center; justify-content:center; height:80vh; text-align:center;">
            <h2 style="font-size:24px; color:#f59e0b; font-weight:700;">🕒 Shop của bạn đang chờ duyệt</h2>
            <p style="color:#6b7280; max-width:480px; font-size:16px; line-height:1.6;">
                Vui lòng chờ quản trị viên phê duyệt trước khi truy cập các chức năng của kênh bán hàng.
            </p>
            <form action="{{ route('logout') }}" method="POST" style="margin-top:20px;">
                @csrf
                <button type="submit"
                    style="background-color:#ef4444; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:600;">
                    🚪 Đăng xuất
                </button>
            </form>
        </div>
    @else
        <!-- ✅ SHOP ĐÃ HOẠT ĐỘNG BÌNH THƯỜNG -->
        <aside class="sidebar">
            <div class="side-section">
                <div class="side-title">Quản Lý Đơn Hàng</div>
                <ul class="menu">
                    <li><a href="#" data-view="orders_all">Tất cả</a></li>
                </ul>
            </div>
            <div class="side-section" style="margin-top:16px;">
                <div class="side-title">Quản Lý Sản Phẩm</div>
                <ul class="menu">
                    <li><a href="#" data-view="products_all">Tất Cả Sản Phẩm</a></li>
                    <li><a href="#" data-view="product_add">Thêm Sản Phẩm</a></li>
                </ul>
            </div>
            <div class="side-section" style="margin-top:16px;">
                <div class="side-title">Quản Lý Voucher</div>
                <ul class="menu">
                    <li><a href="#" data-view="vouchers">Tất cả voucher</a></li>
                    <li><a href="#" data-view="voucher_add">Thêm voucher</a></li>
                </ul>
            </div>
            <div class="side-section" style="margin-top:16px;">
                <div class="side-title" style="font-weight:700;">Thống Kê</div>
                <ul class="menu">
                    <li><a href="#" data-view="revenue_report">💰 Thống kê doanh thu</a></li>
                </ul>
            </div>
            <div class="side-section" style="margin-top:16px;">
                <div class="side-title">Tài Khoản</div>
                <ul class="menu">
                    <li><a href="#" data-view="account_personal">Thông tin tài khoản</a></li>
                    <li>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Đăng xuất</a>
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
                    </li>
                </ul>
            </div>
        </aside>

        <main class="content" id="mainContent">
            @if($shop && $shop->status === 'suspended')
                <div class="suspended-alert">
                    <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
                    <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ.</p>
                </div>
            @endif

            <!-- Giữ nguyên phần dashboard cũ -->
            <div class="card" style="margin-bottom:16px;">
                <h2 style="margin:0 0 8px 0;">Danh sách cần làm</h2>
                <div class="grid">
                    <div class="metric"><h3>Chờ Lấy Hàng</h3><div class="val">0</div></div>
                    <div class="metric"><h3>Đã Xử Lý</h3><div class="val">0</div></div>
                    <div class="metric"><h3>Trả hàng/Hoàn tiền/Hủy</h3><div class="val">0</div></div>
                    <div class="metric"><h3>Sản Phẩm Bị Tạm Khóa</h3><div class="val">0</div></div>
                </div>
            </div>
            <div class="card">
                <h2 style="margin:0 0 8px 0;">Phân Tích Bán Hàng</h2>
                <div class="grid">
                    <div class="metric"><h3>Doanh số</h3><div class="val">0</div></div>
                    <div class="metric"><h3>Lượt truy cập</h3><div class="val">0</div></div>
                    <div class="metric"><h3>Product Clicks</h3><div class="val">0</div></div>
                    <div class="metric"><h3>Đơn hàng</h3><div class="val">0</div></div>
                </div>
            </div>
        </main>
    @endif
</div>


