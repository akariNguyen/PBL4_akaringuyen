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
@php
    $year = request()->get('year', now()->year);
    $sellerId = auth()->id();

    $sellerOrders = \App\Models\Order::whereHas('items', function($q) use ($sellerId) {
        $q->where('seller_id', $sellerId);
    })->whereYear('created_at', $year)
      ->where('status', 'completed')
      ->with(['items' => function($q) use ($sellerId) { $q->where('seller_id', $sellerId); }])
      ->get();

    $totalOrders  = $sellerOrders->count();
    $soldCount    = $sellerOrders->flatMap(fn($o) => $o->items ?? collect())->sum('quantity');
    $totalRevenue = $sellerOrders->sum('total_price');

    // Doanh thu theo tháng
    // ✅ Giới hạn đến tháng hiện tại (chỉ thống kê tháng 1 -> tháng hiện tại)
        // Doanh thu theo tháng
    // ✅ Giới hạn đến tháng hiện tại (chỉ thống kê tháng 1 -> tháng hiện tại)
    $currentMonth = ($year == now()->year) ? now()->month : 12;
    $revenues = [];
    for ($m = 1; $m <= $currentMonth; $m++) {
        $revenues[$m] = \App\Models\Order::whereHas('items', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereYear('created_at', $year)
        ->whereMonth('created_at', $m)
        ->where('status', 'completed')
        ->sum('total_price');
    }
    // ✅ Mảng biểu đồ chỉ chứa tháng 1 -> tháng hiện tại (dùng $currentMonth thay vì 12)
    $revenuesChart = array_values($revenues ?: array_fill(0, $currentMonth, 0));
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
            <!-- <img src="{{ asset('Picture/pending.png') }}" alt="Pending" style="width:120px; height:auto; margin-bottom:20px;"> -->
            <h2 style="font-size:24px; color:#f59e0b; font-weight:700;">🕒 Shop của bạn đang chờ duyệt</h2>
            <p style="color:#6b7280; max-width:480px; font-size:16px; line-height:1.6;">
                Vui lòng chờ quản trị viên phê duyệt trước khi truy cập các chức năng của kênh bán hàng.
            </p>

            <!-- 🔘 Nút đăng xuất -->
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

    <!-- Hidden templates for center content -->
    <template id="tpl-orders-all">
        <?php
            use App\Models\Order;
            use App\Models\OrderItem;
            $sellerId = auth()->id();
            $orders = Order::whereHas('items', function($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })->with(['items' => function($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            }, 'user'])->latest()->get();
            $completedCount = $orders->where('status', 'completed')->count();
            $undeliveredCount = $orders->whereIn('status', ['pending', 'shipped'])->count();
            $cancelledCount = $orders->where('status', 'cancelled')->count();
        ?>
        
        <div class="card" style="margin-bottom:16px;">
            <h2 style="margin:0 0 8px 0;">Thống kê đơn hàng</h2>
            <div class="grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="metric">
                    <h3>Đã giao</h3>
                    <div class="val">{{ $completedCount }}</div>
                </div>
                <div class="metric">
                    <h3>Chưa giao</h3>
                    <div class="val">{{ $undeliveredCount }}</div>
                </div>
                <div class="metric">
                    <h3>Hủy</h3>
                    <div class="val">{{ $cancelledCount }}</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <h2 style="margin:0;">Quản lý tất cả đơn hàng</h2>
                <div style="display:flex; gap:12px;">
                    <input id="filterFrom" type="date" style="padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                    <input id="filterTo" type="date" style="padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                    <button id="btnFilter" class="btn primary">Lọc</button>
                </div>
            </div>
            <div class="tabs">
                <button class="tab active" data-tab="pending">Chờ xử lý</button>
                <button class="tab" data-tab="shipped">Đang giao</button>
                <button class="tab" data-tab="completed">Hoàn thành</button>
                <button class="tab" data-tab="cancelled">Đã hủy</button>
            </div>
            <div id="ordersList">
                <!-- Danh sách đơn hàng sẽ được render động bằng JS -->
            </div>
        </div>
        <!-- Dữ liệu orders JSON để JS xử lý -->
        <script>
            const allOrders = @json($orders);
        </script>
    </template>
        <template id="tpl-products-all">
        <?php
            $sellerProducts = \App\Models\Product::where('seller_id', auth()->id())
                ->with('category')
                ->latest()
                ->get();
        ?>
        @if($shop && $shop->status === 'suspended')
        <div class="suspended-alert">
            <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
            <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết. Các chức năng quản lý có thể bị hạn chế.</p>
        </div>
        @endif
        <div class="card" style="margin-bottom:16px;">
           <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
    <h2 style="margin:0;">Quản lý tất cả sản phẩm</h2>
    <div style="display:flex; align-items:center; gap:8px;">
        <!-- 🔍 Tìm kiếm -->
        <input id="productsSearch" type="text" placeholder="Tìm kiếm theo tên..."
         style="padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; width:220px;">

        <!-- 🏷️ Lọc trạng thái -->
        <select id="statusFilter" style="padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px;">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ duyệt</option>
            <option value="in_stock">Còn hàng</option>
            <option value="rejected">Bị từ chối</option>
        </select>

        <!-- ➕ Nút thêm sản phẩm -->
        <a href="#" onclick="event.preventDefault(); navigate('product_add')"
            style="text-decoration:none; padding:10px 14px; border-radius:8px; background:#2563eb; border:1px solid #2563eb; color:#fff; display:flex; align-items:center;">
            + Thêm sản phẩm
        </a>
    </div>
</div>

        </div>
        <style>
            #productsGrid { display:grid; grid-template-columns: repeat(2, 1fr); gap:16px; }
            .view-text { display:inline; }
            .edit-input { display:none; }
            .save-bar { display:none; }
            .product-card.editing .view-text { display:none; }
            .product-card.editing .edit-input { display:inline-block; }
            .product-card.editing .save-bar { display:flex; gap:8px; margin-top:8px; }
        </style>
        <div class="grid" id="productsGrid">
            @foreach($sellerProducts as $p)
    <?php
        $imgs = is_array($p->images) ? $p->images : json_decode($p->images, true);

        if ($imgs && count($imgs) > 0) {
            // ✅ Làm sạch đường dẫn và build URL chuẩn
            $path = ltrim(str_replace(['\\', '//'], ['', '/'], $imgs[0]), '/');
            $img = asset('storage/' . $path);
        } else {
            // Ảnh mặc định khi không có ảnh
            $img = asset('Picture/products/Aothun.jpg');
        }
        $statusColor = match($p->status){
            'in_stock' => '#16a34a',
            'out_of_stock' => '#dc2626',
            'discontinued' => '#6b7280',
            'pending' => '#f59e0b',
            'rejected' => '#ef4444',
            default => '#6b7280',
        };
        $statusText = match($p->status){
            'in_stock' => 'Còn hàng',
            'out_of_stock' => 'Hết hàng',
            'discontinued' => 'Ngừng kinh doanh',
            'pending' => 'Chờ duyệt',
            'rejected' => 'Bị từ chối',
            default => 'Không xác định',
        };
    ?>
    <form method="post" 
      action="{{ route('products.update', $p->id) }}" 
      class="card product-card product-form" 
      data-status="{{ $p->status }}" 
      style="width:100%;">

        @csrf
        @method('PUT')
        <div style="display:flex; align-items:flex-start; gap:20px; padding:20px;">
            <img src="{{ $img }}" alt="{{ $p->name }}"
                style="width:150px; height:150px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;">
            
            <div style="flex:1;">
                <!-- 🧾 Tên + Trạng thái -->
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:20px; font-weight:600;">
                        <span class="view-text">{{ $p->name }}</span>
                        <input class="edit-input" type="text" name="name" value="{{ $p->name }}">
                    </h3>
                    <span style="font-size:14px; padding:6px 12px; border-radius:999px; background:{{ $statusColor }}20; color:{{ $statusColor }}; border:1px solid {{ $statusColor }}33;">
                        {{ $statusText }}
                    </span>
                </div>

                <!-- 🏷️ Trạng thái sản phẩm -->
                <div style="margin-bottom:16px;">
                    <label style="font-weight:600; color:#6b7280;">Trạng thái:</label>
                    <span class="view-text" style="margin-left:6px; color:#111827; font-weight:600;">{{ $statusText }}</span>
                    <select class="edit-input" name="status" style="padding:8px 10px; border:1px solid #d1d5db; border-radius:8px;">
                        <option value="pending" {{ $p->status=='pending'?'selected':'' }}>Chờ duyệt</option>
                        <option value="in_stock" {{ $p->status=='in_stock'?'selected':'' }}>Còn hàng</option>
                        <option value="rejected" {{ $p->status=='rejected'?'selected':'' }}>Bị từ chối</option>
                    </select>
                </div>

                <!-- 📦 Đã bán + Tồn kho -->
                <div style="display:flex; gap:16px; margin-bottom:16px;">
                    <div style="flex:1; border:1px solid #e5e7eb; border-radius:10px; padding:12px; text-align:center;">
                        <div style="font-weight:700; font-size:18px;">{{ $p->sold_quantity }}</div>
                        <div style="font-size:14px; color:#6b7280;">Đã bán</div>
                    </div>
                    <div style="flex:1; border:1px solid #e5e7eb; border-radius:10px; padding:12px; text-align:center;">
                        <div class="view-text" style="font-weight:700; font-size:18px;">{{ $p->quantity }}</div>
                        <input class="edit-input" type="number" name="quantity" value="{{ $p->quantity }}">
                        <div style="font-size:14px; color:#6b7280;">Tồn kho</div>
                    </div>
                </div>

                <!-- 💰 Giá -->
                <div style="margin-bottom:16px; font-weight:800; color:#16a34a;">
                    <span class="view-text">{{ number_format($p->price, 0, ',', '.') }} VND</span>
                    <input class="edit-input" type="number" name="price" value="{{ $p->price }}">
                </div>

                <!-- 🏷️ Loại -->
                <div style="margin-bottom:16px; font-size:14px; color:#6b7280;">
                    Loại sản phẩm:
                    <span class="view-text" style="color:#111827; font-weight:600;">{{ $p->category?->name ?? '—' }}</span>
                    <input class="edit-input" type="text" name="category" value="{{ $p->category?->name ?? '' }}">
                </div>

                <!-- 🧭 Buttons -->
                <div style="display:flex; justify-content:flex-end; gap:12px;">
                    <!-- ❌ ĐÃ XÓA NÚT XÓA -->
                    <button type="button" class="btn green btn-edit">Chỉnh sửa</button>
                    <div class="save-bar">
                        <button type="submit" class="btn primary">Lưu</button>
                        <button type="button" class="btn btn-cancel">Hủy</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endforeach

        </div>
    </template>
    <template id="tpl-product-add">
        @if($shop && $shop->status === 'suspended')
        <div class="suspended-alert">
            <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
            <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết. Các chức năng quản lý có thể bị hạn chế.</p>
        </div>
        @endif
        <div class="card">
            <h2 style="margin:0 0 12px 0;">Thêm sản phẩm</h2>
            <p style="margin:0 0 16px 0; color:#6b7280;">Nếu loại sản phẩm chưa có, chọn "Khác" và nhập loại mới.</p>
            <form method="post" action="{{ route('products.store') }}" enctype="multipart/form-data" class="product-form">
                @csrf
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Tên sản phẩm</label>
                    <input type="text" name="name" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;" required>
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Loại sản phẩm</label>
                    <select id="category_select"
                            style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">{{ ucfirst($cat->name) }}</option>
                        @endforeach
                        <option value="__other__">Khác</option>
                    </select>
                </div>

                <div id="category_other_wrap" style="display:none;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Loại khác</label>
                    <input type="text" id="category_other" placeholder="Nhập loại sản phẩm"
                        style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;">
                </div>

                <input type="hidden" name="category" id="category_value">

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Mô tả chi tiết</label>
                    <textarea name="description" rows="4" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;"></textarea>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Giá bán (VNĐ)</label>
                        <input type="number" step="0.01" name="price" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;" required>
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Số lượng</label>
                        <input type="number" name="quantity" style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px;" required>
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Hình ảnh (có thể chọn nhiều)</label>
                    <input type="file" name="images[]" accept="image/*" multiple required>

                </div>
                <div>
                    <button type="submit" style="padding:10px 16px; border-radius:8px; border:1px solid #2563eb; background:#2563eb; color:#fff;">Tạo sản phẩm</button>
                </div>
            </form>
        </div>
    </template>
    <template id="tpl-account-personal">
        
        <div class="card">
            @if(session('success'))
                <div class="success-message">{{ session('success') }}</div>
            @endif
            @if(session('success_password'))
                <div class="success-message">{{ session('success_password') }}</div>
            @endif
            <div class="tabs">
                <button class="tab active" data-tab="info">Thông tin</button>
                <button class="tab" data-tab="password">Đổi mật khẩu</button>
            </div>

            <!-- Thông tin cá nhân -->
            <div id="tab-info" class="section">
                <!-- ✅ Đã chỉnh đường dẫn tuyệt đối -->
                <form id="formInfo" method="post" action="/account/personal" enctype="multipart/form-data">
                    @csrf
                    <div class="row" style="justify-content:space-between; margin-bottom:16px;">
                        <h3 style="margin:0;">Thông tin cá nhân</h3>
                        <button type="button" id="btnEdit" class="icon-btn" title="Chỉnh sửa">✎</button>
                    </div>
                    <div class="row">
    <div class="label">Avatar</div>
    <div>
        @php
            use Illuminate\Support\Str;

            $user = auth()->user();
            $avatarPath = $user->avatar_path;

            if ($avatarPath) {
                // Nếu là ảnh upload trong storage
                if (Str::startsWith($avatarPath, 'avatars/')) {
                    $avatarUrl = asset('storage/' . $avatarPath);
                } else {
                    // Nếu là đường dẫn tuyệt đối /Picture/... thì giữ nguyên
                    $avatarUrl = asset($avatarPath);
                }
            } else {
                // Nếu không có ảnh → dùng ảnh mặc định theo giới tính
                $avatarUrl = $user->gender === 'female'
                    ? asset('/Picture/Avata/avatar_macdinh_nu.jpg')
                    : asset('/Picture/Avata/avatar_macdinh_nam.jpg');
            }
        @endphp

        <img id="avatar_img" src="{{ $avatarUrl }}" class="avatar" alt="avatar">
        <input id="avatar_input" type="file" name="avatar" accept="image/*" class="edit-input" style="margin-left:12px;">
    </div>
</div>

                    <div class="row">
                        <div class="label">Họ tên</div>
                        <div>
                            <span id="name_text" class="view-text">{{ auth()->user()->name }}</span>
                            <input class="edit-input" type="text" name="name" value="{{ auth()->user()->name }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="label">Số điện thoại</div>
                        <div>
                            <span class="view-text">{{ auth()->user()->phone ?? '—' }}</span>
                            <input class="edit-input" type="text" name="phone" value="{{ auth()->user()->phone ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="label">Email</div>
                        <div class="inline">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Giới tính</div>
                        <div class="inline">{{ auth()->user()->gender === 'female' ? 'Nữ' : 'Nam' }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Vai trò</div>
                        <div class="inline">{{ auth()->user()->role }}</div>
                    </div>
                    <div class="row">
                        <div class="label">Trạng thái</div>
                        <div class="inline">{{ auth()->user()->status }}</div>
                    </div>
                    @if ($errors->any())
                        <div style="background:#fee2e2; color:#dc2626; padding:10px 12px; border:1px solid #fecaca; border-radius:8px; margin-top:8px;">
                            <ul style="margin:0; padding-left:16px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="save-bar">
                        <button type="submit" class="btn primary">Lưu</button>
                        <button type="button" id="btnCancel" class="btn">Hủy</button>
                    </div>
                </form>
                <p class="hint" style="margin-top:8px;">Bấm biểu tượng ✎ để chỉnh sửa trực tiếp họ tên, số điện thoại và avatar. Chọn ảnh để xem trước, chỉ lưu khi bấm Lưu.</p>
            </div>

            <!-- Đổi mật khẩu -->
            <div id="tab-password" class="section" style="display:none;">
                <!-- ✅ Đã chỉnh đường dẫn tuyệt đối -->
                <form id="formPassword" method="post" action="/account/password">
                    @csrf
                    <div class="row">
                        <div class="label">Mật khẩu hiện tại</div>
                        <div class="inline"><input type="password" name="current_password" required></div>
                    </div>
                    <div class="row">
                        <div class="label">Mật khẩu mới</div>
                        <div class="inline"><input type="password" name="password" required></div>
                    </div>
                    <div class="row">
                        <div class="label">Xác nhận mật khẩu</div>
                        <div class="inline"><input type="password" name="password_confirmation" required></div>
                    </div>
                    <div class="actions" style="margin-top:8px;"><button type="submit" class="btn primary">Lưu</button></div>
                    @if ($errors->any())
                        <div style="background:#fee2e2; color:#dc2626; padding:10px 12px; border:1px solid #fecaca; border-radius:8px; margin-top:8px;">
                            <ul style="margin:0; padding-left:16px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Thông tin shop -->
        <div class="card">
            @if(session('success'))
                <div class="success-message">{{ session('success') }}</div>
            @endif
            @php
    use Illuminate\Support\Facades\Storage;
    use App\Models\Shop;

    // ✅ Lấy shop theo user_id (không dùng find)
    $shop = Shop::where('user_id', auth()->id())->first();
@endphp

<form id="formShop" method="post" action="/account/shop" enctype="multipart/form-data">
    @csrf
    <div class="row" style="justify-content:space-between; margin-bottom:16px;">
        <h3 style="margin:0;">Thông tin shop</h3>
        <button type="button" id="btnEditShop" class="icon-btn" title="Chỉnh sửa">✎</button>
    </div>

    <div class="row">
        <div class="label">Logo</div>
        <div>
            @if($shop && $shop->logo_path && Storage::disk('public')->exists($shop->logo_path))
                {{-- ✅ Logo trong storage/public/shops --}}
                <img id="logo_img" src="{{ Storage::url($shop->logo_path) }}" class="logo" alt="logo">
            @else
                {{-- 🖼️ Logo mặc định --}}
                <img id="logo_img" src="{{ asset('Picture/Logo.png') }}" class="logo" alt="logo">
            @endif
            <input id="logo_input" type="file" name="logo" accept="image/*" class="edit-input" style="margin-left:12px;">
        </div>
    </div>

                <div class="row">
                    <div class="label">Tên shop</div>
                    <div>
                        <span class="view-text">{{ $shop->name ?? '—' }}</span>
                        <input class="edit-input" type="text" name="name" value="{{ $shop->name ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="label">Mô tả</div>
                    <div>
                        <span class="view-text">{{ $shop->description ?? '—' }}</span>
                        <textarea class="edit-input" name="description" rows="3" style="width:100%; max-width:480px;">{{ $shop->description ?? '' }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="label">Trạng thái</div>
                    <div>
                        <span class="view-text" id="shop_status">{{ $shop->status ?? '—' }}</span>
                        <select class="edit-input" name="status" disabled style="display:none;">
                            <option value="active" {{ ($shop && $shop->status=='active') ? 'selected' : '' }}>Hoạt động</option>
                            <option value="closed" {{ ($shop && $shop->status=='closed') ? 'selected' : '' }}>Đóng cửa</option>
                        </select>
                    </div>
                </div>

                @if ($errors->any())
                    <div style="background:#fee2e2; color:#dc2626; padding:10px 12px; border:1px solid #fecaca; border-radius:8px; margin-top:8px;">
                        <ul style="margin:0; padding-left:16px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="save-bar">
                    <button type="submit" class="btn primary">Lưu</button>
                    <button type="button" id="btnCancelShop" class="btn">Hủy</button>
                </div>
            </form>
        </div>
</template>

    <template id="tpl-vouchers">
<?php
    $shop = \App\Models\Shop::where('user_id', auth()->id())->first();
    $vouchers = $shop ? \App\Models\Voucher::where('shop_id', $shop->user_id)->latest()->get() : collect();
?>
       
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2>🎟️ Quản lý Voucher</h2>
        <a href="#" onclick="event.preventDefault(); navigate('voucher_add')" class="btn primary">+ Thêm Voucher</a>
    </div>
    <table style="width:100%; border-collapse:separate; border-spacing:0 8px;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:12px;">Mã</th>
                <th style="padding:12px;">Giảm giá</th>
                <th style="padding:12px;">Hết hạn</th>
                <th style="padding:12px;">Trạng thái</th>
                <th style="padding:12px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $v)
            <tr data-id="{{ $v->id }}" style="background:#fff; border:1px solid #e5e7eb;">
                <td style="padding:12px;">{{ $v->code }}</td>
                <td style="padding:12px;"><input type="number" value="{{ $v->discount_amount }}" style="width:100px; text-align:center;"></td>
                <td style="padding:12px;"><input type="date" value="{{ $v->expiry_date->format('Y-m-d') }}"></td>
                <td style="padding:12px;">
                    <select>
                        <option value="active" {{ $v->status=='active'?'selected':'' }}>Hoạt động</option>
                        <option value="expired" {{ $v->status=='expired'?'selected':'' }}>Hết hạn</option>
                    </select>
                </td>
                <td style="padding:12px;">
                    <button class="btn green btn-save">Lưu</button>
                    <button class="btn red btn-delete">Xóa</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</template>
<template id="tpl-voucher-add">
        @if($shop && $shop->status === 'suspended')
        <div class="suspended-alert">
            <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
            <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết. Các chức năng quản lý có thể bị hạn chế.</p>
        </div>
        @endif
<div class="card">
    <h2 style="margin-bottom:12px;">➕ Thêm Voucher Mới</h2>
    <form id="voucherAddForm">
        @csrf
        <div style="margin-bottom:12px;">
            <label>Mã voucher</label>
            <input type="text" name="code" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;" required>
        </div>
        <div style="margin-bottom:12px;">
            <label>Số tiền giảm (VNĐ)</label>
            <input type="number" name="discount_amount" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;" required>
        </div>
        <div style="margin-bottom:12px;">
            <label>Ngày hết hạn</label>
            <input type="date" name="expiry_date" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;" required>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn green">Lưu</button>
            <a href="#" onclick="event.preventDefault(); navigate('vouchers')" class="btn">Quay lại</a>
        </div>
    </form>
</div>
</template>
<template id="tpl-revenue-report">
    @if($shop && $shop->status === 'suspended')
    <div class="suspended-alert">
        <h3 style="margin:0 0 8px 0; font-weight:600;">Shop đã bị đình chỉ</h3>
        <p style="margin:0; font-size:14px;">Shop của bạn đã bị đình chỉ hoạt động. Vui lòng liên hệ với bộ phận hỗ trợ để được giải quyết. Các chức năng quản lý có thể bị hạn chế.</p>
    </div>
    @endif

    <div class="card" style="margin-bottom:20px;">
        <h2 style="margin-bottom:16px;">📊 Thống kê doanh thu</h2>

        <!-- Bộ lọc năm -->
        <!-- Bộ lọc năm -->
<form id="yearForm" style="margin-bottom:20px; display:flex; align-items:center; gap:12px;">
    <label for="yearSelect">Năm:</label>
    <select id="yearSelect" name="year"
        style="padding:8px 12px; border-radius:8px; border:1px solid #d1d5db;">
        @for($y = now()->year; $y >= now()->year - 5; $y--)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>
</form>



        <!-- Thống kê tổng quan -->
        <div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom:24px;">
            <div class="metric">
                <h3>🧾 Số đơn hoàn tất</h3>
                <div class="val">{{ $totalOrders ?? 0 }}</div>
            </div>
            <div class="metric">
                <h3>📦 Sản phẩm đã bán</h3>
                <div class="val">{{ $soldCount ?? 0 }}</div>
            </div>
            <div class="metric">
                <h3>💰 Tổng doanh thu</h3>
                <div class="val" style="color:#16a34a;">
                    {{ number_format($totalRevenue ?? 0, 0, ',', '.') }} ₫
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu -->
        <!-- 🧩 Biểu đồ doanh thu -->
<div class="card" style="padding:16px; height:380px;">
    <canvas id="chartRevenue" style="width:100%; height:100%;"></canvas>
</div>

    </div>

    <!-- Chart.js -->
   
</template>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
const shopStatus = @json($shop ? $shop->status : 'active');
if (shopStatus === 'pending') {
    console.log("Shop đang chờ duyệt — bỏ qua JavaScript dashboard.");
} else {
(function(){
    function show(viewId){
        var main = document.getElementById('mainContent');
        if(!main) {
            console.error('Main content element not found');
            return;
        }
        main.innerHTML = '';
        var tpl = document.getElementById(viewId);
        if (tpl) {
            main.appendChild(tpl.content.cloneNode(true));
        } else {
            console.error('Template not found:', viewId);
        }
    }
    function navigate(view){
        var map = {
            'orders_all': 'tpl-orders-all',
            'orders_bulk': 'tpl-orders-all',
            
            'shipping': 'tpl-orders-all',
            'products_all': 'tpl-products-all',
            'product_add': 'tpl-product-add',
            'account_personal': 'tpl-account-personal',
            'vouchers': 'tpl-vouchers',
            'voucher_add': 'tpl-voucher-add',
             'revenue_report': 'tpl-revenue-report',
        };
        show(map[view] || 'tpl-orders-all');


        history.pushState({}, '', `/seller/${view.replace('_', '/')}`);

        setTimeout(function(){
            if (view === 'product_add') bindCategory();
            if (view === 'products_all') bindProductsSearch();
            if (view === 'account_personal') bindAccountPersonal();
            if (view === 'account_personal') bindAccountShop();
            if (view === 'orders_all') bindOrders();
            if (view === 'vouchers') bindVouchers();
            if (view === 'voucher_add') bindVoucherAdd();
            if (view === 'revenue_report') bindRevenueChart();
        }, 0);
    }
    function getParameterByName(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    (function init(){
    console.log('Initializing dashboard...');
    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    const path = window.location.pathname;

    if (redirect === 'account_personal') {
        navigate('account_personal');
        return;
    }

    if (path.includes('/seller/vouchers')) {
        navigate('vouchers');
    } else if (path.includes('/seller/vouchers/create')) {
        navigate('voucher_add');
    } else if (path.includes('/seller/dashboard')) {
        navigate('orders_all');
    } else {
        navigate('orders_all');
    }
})();

    document.querySelectorAll('.sidebar a[data-view]').forEach(function(a){
        a.addEventListener('click', function(e){
            e.preventDefault();
            navigate(a.getAttribute('data-view'));
        });
    });
    function bindCategory(){
        var sel = document.getElementById('category_select');
        var otherWrap = document.getElementById('category_other_wrap');
        var other = document.getElementById('category_other');
        var hidden = document.getElementById('category_value');
        if(!sel || !hidden) {
            console.error('Category select or hidden input not found');
            return;
        }
        function sync(){
            if (sel.value === '__other__') {
                otherWrap.style.display = 'block';
                hidden.value = (other && other.value.trim()) ? other.value.trim() : '';
            } else {
                otherWrap.style.display = 'none';
                hidden.value = sel.value;
            }
        }
        sel.addEventListener('change', sync);
        if (other) other.addEventListener('input', sync);
        sync();
        // Bind submit for add product
        const addForm = document.querySelector('.product-form[action*="products/store"]');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                if (shopStatus === 'suspended') {
                    e.preventDefault();
                    alert('Shop đã bị đình chỉ. Không thể thêm sản phẩm mới.');
                    return false;
                }
            });
        }
    }
    function bindProductsSearch() {
    const input = document.getElementById('productsSearch');
    const grid = document.getElementById('productsGrid');
    const statusFilter = document.getElementById('statusFilter');
    if (!grid) return;

    // 🔍 Lọc theo tên sản phẩm
    if (input) {
        input.addEventListener('input', function () {
            const q = input.value.trim().toLowerCase();
            grid.querySelectorAll('.card.product-card').forEach(card => {
                const name = (card.querySelector('h3 span.view-text')?.textContent || '').toLowerCase();
                card.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        });
    }

    // 🏷️ Lọc theo trạng thái
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            const selected = statusFilter.value.trim().toLowerCase();
            grid.querySelectorAll('.card.product-card').forEach(card => {
                const status = (card.dataset.status || '').toLowerCase();
                card.style.display = (!selected || status === selected) ? '' : 'none';
            });
        });
    }
} // <-- BỔ SUNG DẤU ĐÓNG

    function bindAccountPersonal(){
        var tabs = document.querySelectorAll('.tab');
        tabs.forEach(function(btn){
            btn.addEventListener('click', function(){
                tabs.forEach(function(b){ b.classList.remove('active'); });
                btn.classList.add('active');
                var tab = btn.getAttribute('data-tab');
                document.getElementById('tab-info').style.display = (tab==='info')?'block':'none';
                document.getElementById('tab-password').style.display = (tab==='password')?'block':'none';
            });
        });
        var form = document.getElementById('formInfo');
        if(form){
            var btnEdit = document.getElementById('btnEdit');
            var btnCancel = document.getElementById('btnCancel');
            if (btnEdit) {
    btnEdit.addEventListener('click', function (e) {
        if (shopStatus === 'suspended') {
            e.preventDefault();
            alert('🚫 Shop đang bị đình chỉ — không thể chỉnh sửa thông tin cá nhân.');
            return;
        }
        form.classList.add('editing');
    });
}

            if(btnCancel) btnCancel.addEventListener('click', function(){ form.classList.remove('editing'); });
            var avatarInput = document.getElementById('avatar_input');
            var avatarImg = document.getElementById('avatar_img');
            if(avatarInput && avatarImg){
                avatarInput.addEventListener('change', function(){
                    if(this.files && this.files[0]){
                        var reader = new FileReader();
                        reader.onload = function(e){ avatarImg.src = e.target.result; }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
            form.addEventListener('submit', function(e){
                e.preventDefault();
                var formData = new FormData(form);
                fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        form.classList.remove('editing');
                        var successDiv = document.createElement('div');
                        successDiv.className = 'success-message';
                        successDiv.textContent = data.message;
                        form.parentElement.insertBefore(successDiv, form);
                        if(data.name) document.getElementById('name_text').textContent = data.name;
                        if(data.avatar) document.getElementById('avatar_img').src = data.avatar;
                        window.location.href = '/seller/dashboard?redirect=account_personal';


                    } else {
                        alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    alert('Có lỗi xảy ra khi gửi yêu cầu.');
                });
            });
        }
        var formPassword = document.getElementById('formPassword');
        if (formPassword) {
            formPassword.addEventListener('submit', function(e){
                e.preventDefault();
                var formData = new FormData(formPassword);
                fetch(formPassword.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var successDiv = document.createElement('div');
                        successDiv.className = 'success-message';
                        successDiv.textContent = data.message;
                        formPassword.parentElement.insertBefore(successDiv, formPassword);
                       window.location.href = '/seller/dashboard?redirect=account_personal';

                    } else {
                        alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
                    }
                })
                .catch(error => {
                    console.error('Password form submission error:', error);
                    alert('Có lỗi xảy ra khi gửi yêu cầu.');
                });
            });
        }
    }
    function bindAccountShop() {
    var form = document.getElementById('formShop');
    if(!form) {
        console.error('Shop form not found');
        return;
    }

    var btnEdit = document.getElementById('btnEditShop');
    var btnCancel = document.getElementById('btnCancelShop');

    if (btnEdit) {
        btnEdit.addEventListener('click', function (e) {
            if (shopStatus === 'suspended') {
                e.preventDefault();
                alert('🚫 Shop của bạn đang bị đình chỉ — không thể chỉnh sửa thông tin.');
                return;
            }
            form.classList.add('editing');
        });
    }

    if(btnCancel) btnCancel.addEventListener('click', function(){ form.classList.remove('editing'); });

    var logoInput = document.getElementById('logo_input');
    var logoImg = document.getElementById('logo_img');

    if(logoInput && logoImg){
        logoInput.addEventListener('change', function(){
            if(this.files && this.files[0]){
                var reader = new FileReader();
                reader.onload = function(e){ logoImg.src = e.target.result; }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();
        var formData = new FormData(form);

        fetch(form.getAttribute('action'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                form.classList.remove('editing');
                var successDiv = document.createElement('div');
                successDiv.className = 'success-message';
                successDiv.textContent = data.message;
                form.parentElement.insertBefore(successDiv, form);

                if(data.name) form.querySelector('.view-text').textContent = data.name;
                if(data.logo) document.getElementById('logo_img').src = data.logo;

                // 🔁 Reload về seller dashboard và mở lại tab account_personal
                window.location.href = '/seller/dashboard?redirect=account_personal';
            } else {
                alert('Có lỗi xảy ra: ' + (data.errors ? Object.values(data.errors).join(', ') : ''));
            }
        })
        .catch(error => {
            console.error('Shop form submission error:', error);
            alert('Có lỗi xảy ra khi gửi yêu cầu.');
        });
    });
}

    function renderOrders(status, fromDate = null, toDate = null) {
        console.log('Rendering orders for status:', status, 'from:', fromDate, 'to:', toDate);
        const list = document.getElementById('ordersList');
        if (!list) {
            console.error('Orders list element not found');
            alert('Lỗi: Không tìm thấy danh sách đơn hàng.');
            return;
        }
        list.innerHTML = '';
        console.log('Available orders:', allOrders); // Debug
        let startDate = fromDate ? new Date(fromDate) : null;
        let endDate = toDate ? new Date(toDate) : null;
        if (startDate) startDate.setHours(0, 0, 0, 0);
        if (endDate) endDate.setHours(23, 59, 59, 999);
        const filteredOrders = allOrders.filter(order => {
            if (!order) {
                console.warn('Invalid order:', order);
                return false;
            }
            if (status && order.status !== status) return false;
            const orderDate = new Date(order.created_at);
            if (startDate && orderDate < startDate) return false;
            if (endDate && orderDate > endDate) return false;
            return true;
        });
        console.log('Filtered orders:', filteredOrders); // Debug
        if (filteredOrders.length === 0) {
            list.innerHTML = '<p style="color:#6b7280; margin:0;">Chưa có đơn hàng phù hợp.</p>';
            return;
        }
        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'separate';
        table.style.borderSpacing = '0 8px';
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr style="background:#f3f4f6; text-align:left;">
                <th style="padding:12px;">Mã đơn</th>
                <th style="padding:12px;">Khách hàng</th>
                <th style="padding:12px;">Tổng giá</th>
                <th style="padding:12px;">Sản phẩm</th>
                <th style="padding:12px;">Trạng thái</th>
                <th style="padding:12px;">Ngày tạo</th>
                <th style="padding:12px;">Hành động</th>
            </tr>
        `;
        table.appendChild(thead);
        const tbody = document.createElement('tbody');
        filteredOrders.forEach(order => {
            if (!order.id) {
                console.error('Invalid order data:', order);
                return;
            }
            const row = document.createElement('tr');
            row.style.background = '#fff';
            row.style.border = '1px solid #e5e7eb';
            row.style.borderRadius = '8px';
            let itemsHtml = '';
            if (order.items && Array.isArray(order.items)) {
                order.items.forEach(item => {
                    itemsHtml += `<div>${item.product_name || '—'} x ${item.quantity || 0} (${numberFormat(item.price || 0)} VND)</div>`;
                });
            } else {
                console.warn('No items for order:', order.id);
                itemsHtml = '<div>—</div>';
            }
            let actionHtml = '';
            let statusText = '';
            let statusColor = '#e5e7eb';
            switch (order.status) {
                case 'pending':
                    actionHtml = `
                        <button onclick="updateOrderStatus(${order.id}, this)" class="btn green" style="padding:6px 12px; border-radius:4px;" data-order-id="${order.id}" data-original-text="Xử lý đơn hàng">Xử lý đơn hàng</button>
                    `;
                    statusText = 'Chờ xử lý';
                    statusColor = '#f59e0b';
                    break;
                case 'shipped':
                    actionHtml = `
                        <button onclick="markDelivered(${order.id}, this)" class="btn orange" style="padding:6px 12px; border-radius:4px;" data-order-id="${order.id}" data-original-text="Giao hàng thành công">Giao hàng thành công</button>
                    `;
                    statusText = 'Đang giao';
                    statusColor = '#92400e';
                    break;
                case 'completed':
                    actionHtml = `
                        <span style="padding:4px 8px; border-radius:4px; background:#d1d5db; color:#6b7280; font-size:12px;">Hoàn thành</span>
                    `;
                    statusText = 'Hoàn thành';
                    statusColor = '#6b7280';
                    break;
                case 'cancelled':
                    actionHtml = `
                        <span style="padding:4px 8px; border-radius:4px; background:#fecaca; color:#dc2626; font-size:12px;">Đã hủy</span>
                    `;
                    statusText = 'Đã hủy';
                    statusColor = '#dc2626';
                    break;
                default:
                    console.warn('Unknown order status:', order.status);
                    statusText = 'Không xác định';
            }
            row.innerHTML = `
                <td style="padding:12px;">#${order.id}</td>
                <td style="padding:12px;">${order.user?.name || '—'}</td>
                <td style="padding:12px;">${numberFormat(order.total_price || 0)} VND</td>
                <td style="padding:12px;">${itemsHtml}</td>
                <td style="padding:12px;"><span style="padding:4px 8px; border-radius:4px; background:${statusColor}20; color:${statusColor};">${statusText}</span></td>
                <td style="padding:12px;">${new Date(order.created_at).toLocaleDateString('vi-VN')}</td>
                <td style="padding:12px;">${actionHtml}</td>
            `;
            tbody.appendChild(row);
        });
        table.appendChild(tbody);
        list.appendChild(table);
    }
    function numberFormat(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function updateOrderStatus(orderId, button) {
        console.log('Clicked Xử lý đơn hàng for order:', orderId);
        if (!button) {
            console.error('Button element missing for order:', orderId);
            alert('Lỗi: Không tìm thấy nút xử lý.');
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found');
            alert('Lỗi: Không tìm thấy CSRF token.');
            return;
        }
        button.disabled = true;
        button.textContent = 'Đang xử lý...';
        console.log('Sending AJAX to update order:', orderId, 'to status: shipped');
        fetch(`/orders/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: 'shipped' })
        })
        .then(response => {
            console.log('Server response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response data:', data);
            if (data.success) {
                const successDiv = document.createElement('div');
                successDiv.className = 'success-message';
                successDiv.textContent = 'Đã chuyển trạng thái sang Đang giao!';
                successDiv.style.marginBottom = '12px';
                const list = document.getElementById('ordersList');
                if (list) {
                    list.parentElement.insertBefore(successDiv, list);
                }
                // Cập nhật trạng thái trong allOrders
                const order = allOrders.find(o => o.id === orderId);
                if (order) {
                    order.status = 'shipped';
                }
                // Tự động chuyển sang tab "Đang giao" và làm mới danh sách
                const shippedTab = document.querySelector('.tab[data-tab="shipped"]');
                const tabs = document.querySelectorAll('.tab[data-tab]');
                tabs.forEach(t => t.classList.remove('active'));
                if (shippedTab) shippedTab.classList.add('active');
                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders('shipped', from, to);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái.'));
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
            alert('Lỗi khi gửi yêu cầu: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Xử lý đơn hàng';
        });
    }
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit')) {
            const card = e.target.closest('.product-card');
            card.classList.add('editing');
        }
        if (e.target.classList.contains('btn-cancel')) {
            const card = e.target.closest('.product-card');
            card.classList.remove('editing');
        }
    });
    // Expose to global so inline onclick can access
    window.updateOrderStatus = updateOrderStatus;
    function markDelivered(orderId, button) {
        console.log('Clicked Giao hàng thành công for order:', orderId);
        if (!button) {
            alert('Lỗi: Không tìm thấy nút.');
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            alert('Lỗi: Thiếu CSRF token');
            return;
        }
        button.disabled = true;
        const original = button.textContent;
        button.textContent = 'Đang cập nhật...';
        fetch(`/orders/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: 'completed' })
        })
        .then(r => {
            if (!r.ok) return r.json().then(e => { throw new Error(e.message || 'Cập nhật thất bại'); });
            return r.json();
        })
        .then(data => {
            if (data.success) {
                const order = allOrders.find(o => o.id === orderId);
                if (order) order.status = 'completed';

                // 🟩 Cập nhật ngay phần thống kê số lượng trên dashboard
                // 🟩 Cập nhật ngay phần thống kê số lượng trên dashboard (sau khi giao hàng)
                document.querySelectorAll('.metric').forEach(metric => {
                    const title = metric.querySelector('h3')?.textContent?.trim();

                    if (title === 'Đã giao') {
                        const val = metric.querySelector('.val');
                        if (val) val.textContent = parseInt(val.textContent) + 1;
                    }
                    if (title === 'Chưa giao') {
                        const val = metric.querySelector('.val');
                        if (val && parseInt(val.textContent) > 0)
                            val.textContent = parseInt(val.textContent) - 1;
                    }
                });
                        

                // 🟦 Chuyển tab sang "Hoàn thành"
                const completedTab = document.querySelector('.tab[data-tab="completed"]');
                const tabs = document.querySelectorAll('.tab[data-tab]');
                tabs.forEach(t => t.classList.remove('active'));
                if (completedTab) completedTab.classList.add('active');

                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders('completed', from, to);
            } else {
                alert(data.message || 'Không thể cập nhật.');
            }
        })

        .catch(err => {
            console.error(err);
            alert(err.message);
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = original;
        });
    }
    window.markDelivered = markDelivered;
    function bindOrders() {
        console.log('Binding orders tab...');
        const tabs = document.querySelectorAll('.tab[data-tab]');
        let currentStatus = 'pending';
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                console.log('Tab clicked:', tab.getAttribute('data-tab'));
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentStatus = tab.getAttribute('data-tab');
                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders(currentStatus, from, to);
            });
        });
        const btnFilter = document.getElementById('btnFilter');
        if (btnFilter) {
            btnFilter.addEventListener('click', () => {
                console.log('Filter button clicked');
                const from = document.getElementById('filterFrom').value;
                const to = document.getElementById('filterTo').value;
                renderOrders(currentStatus, from, to);
            });
        }
        renderOrders('pending');
    }
    window.navigate = navigate;
    function bindVouchers() {
    // ✅ Lấy tbody thực trong DOM
    const tbody = document.querySelector('#mainContent tbody');
    if (!tbody) {
        console.warn('Không tìm thấy tbody trong giao diện voucher');
        return;
    }
    fetch('/seller/vouchers/json')
        .then(res => {
            if (!res.ok) throw new Error('Server trả lỗi ' + res.status);
            return res.json();
        })
        .then(vouchers => {
            tbody.innerHTML = '';
            if (!vouchers.length) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">
                        ⚠️ Chưa có voucher nào.
                    </td></tr>`;
                return;
            }
            vouchers.forEach(v => {
                const row = document.createElement('tr');
                row.dataset.id = v.id;
                // Chuẩn hoá ngày từ "2025-11-15T00:00:00Z" -> "2025-11-15"
                const expiry = (v.expiry_date || '').toString().split('T')[0] || '';
                row.innerHTML = `
                    <td style="padding:12px;">${v.code}</td>
                    <td style="padding:12px;">
                        <input type="number" value="${v.discount_amount}" style="width:100px;text-align:center;" disabled>
                    </td>
                    <td style="padding:12px;">
                        <input type="date" value="${expiry}" disabled>
                    </td>
                    <td style="padding:12px;">
                        <select disabled>
                            <option value="active" ${v.status === 'active' ? 'selected' : ''}>Hoạt động</option>
                            <option value="expired" ${v.status === 'expired' ? 'selected' : ''}>Hết hạn</option>
                        </select>
                    </td>
                    <td style="padding:12px;">
                        <button class="btn orange btn-edit-voucher">Sửa</button>
                        <button class="btn red btn-delete">Xóa</button>
                    </td>`;
                tbody.appendChild(row);
            });
            // 🔴 XÓA
            // 🔴 XÓA
tbody.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        if (shopStatus === 'suspended') {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể xóa voucher.');
            return;
        }

        const row = btn.closest('tr');
        const id = row.dataset.id;
        if (!confirm('⚠️ Bạn có chắc muốn xóa voucher này không?')) return;

        try {
            const res = await fetch(`/seller/vouchers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await res.json();
            if (data.success) {
                alert('🗑️ Đã xóa voucher thành công!');
                bindVouchers(); // reload
            } else {
                alert('❌ ' + (data.message || 'Không thể xóa voucher!'));
            }
        } catch (err) {
            console.error('Lỗi khi xóa voucher:', err);
            alert('⚠️ Lỗi kết nối server!');
        }
    });
});

// 🟠 SỬA / 💾 LƯU
tbody.querySelectorAll('.btn-edit-voucher').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        if (shopStatus === 'suspended') {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể chỉnh sửa voucher.');
            return;
        }

        const row = btn.closest('tr');
        const inputs = row.querySelectorAll('input, select');
        const id = row.dataset.id;

        if (btn.textContent === 'Sửa') {
            // 🔓 Cho phép chỉnh sửa
            inputs.forEach(i => i.disabled = false);
            btn.textContent = 'Lưu';
            btn.classList.remove('orange');
            btn.classList.add('green');
            return;
        }

        // 💾 Lưu thay đổi
        const discount = row.querySelector('input[type="number"]').value.trim();
        const expiry = row.querySelector('input[type="date"]').value;
        const status = row.querySelector('select').value;
        try {
            const res = await fetch(`/seller/vouchers/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    discount_amount: discount,
                    expiry_date: expiry,
                    status: status
                })
            });
            const data = await res.json();
            if (data.success) {
                alert('✅ Cập nhật voucher thành công!');
                inputs.forEach(i => i.disabled = true);
                btn.textContent = 'Sửa';
                btn.classList.remove('green');
                btn.classList.add('orange');
            } else {
                alert('❌ ' + (data.message || 'Không thể cập nhật voucher!'));
            }
        } catch (err) {
            console.error('Lỗi khi cập nhật voucher:', err);
            alert('⚠️ Lỗi kết nối server!');
        }
    });
});

        })
        .catch(err => {
            console.error('Lỗi khi tải voucher:', err);
            alert('⚠️ Không thể tải danh sách voucher.');
        });
}
function bindVoucherAdd() {
    const form = document.getElementById('voucherAddForm');
    if (!form) return;
    form.onsubmit = async e => {
        e.preventDefault();
        const data = new FormData(form);
        try {
            const res = await fetch('/seller/vouchers', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            });
            if (!res.ok) throw new Error(`Server trả lỗi ${res.status}`);
            const d = await res.json();
            if (d.success) {
                alert('✅ Thêm voucher thành công!');
                window.location.href = '/seller/vouchers';
            } else {
                alert('❌ Có lỗi xảy ra khi thêm voucher!');
            }
        } catch (err) {
            console.error('❌ Lỗi khi thêm voucher:', err);
            alert('⚠️ Lỗi kết nối đến server hoặc phản hồi không hợp lệ!');
        }
    };
}
function bindRevenueChart() {
    const ctx = document.getElementById('chartRevenue');
    if (!ctx) return;

    const yearSelect = document.getElementById('yearSelect');
    let chartInstance = null;

    // ✅ Hàm vẽ lại biểu đồ
    const renderChart = (revenues, year) => {
        if (chartInstance) chartInstance.destroy();

        const months = Array.from({length: revenues.length}, (_, i) => 'T' + (i + 1));

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: `Doanh thu ${year}`,
                    data: revenues,
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                    barThickness: 'flex'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: 10 },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => new Intl.NumberFormat('vi-VN').format(v) + ' ₫'
                        }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
    datalabels: {
        anchor: 'end',          // điểm neo ở đỉnh cột
        align: 'end',           // căn phía trên đỉnh
        offset: -6,             // đẩy lên cao 6px để tách khỏi cột
        color: '#111',
        font: { weight: '600', size: 13 },
        formatter: v => v > 0 ? new Intl.NumberFormat('vi-VN').format(v) + ' ₫' : ''
    }
}

            },
            plugins: [ChartDataLabels]
        });
    };

    // ✅ Lần đầu vẽ theo dữ liệu Blade render sẵn
    const revenuesBlade = {!! json_encode($revenuesChart ?? []) !!};
    renderChart(revenuesBlade, {{ $year }});

    // ✅ Khi chọn năm khác → chỉ gọi API JSON → update chart
    if (yearSelect && !yearSelect.dataset.bound) {
        yearSelect.dataset.bound = 'true';
        yearSelect.addEventListener('change', e => {
            const year = e.target.value;
            fetch(`/seller/revenue/json?year=${year}`)
                .then(res => res.json())
                .then(data => {
                    renderChart(data.revenues, data.year);
                });
        });
    }
}

})();
// 🔒 KHÓA CHỨC NĂNG KHI SHOP ĐANG BỊ ĐÌNH CHỈ
// 🔒 KHÓA CHỨC NĂNG KHI SHOP ĐANG BỊ ĐÌNH CHỈ
if (shopStatus === 'suspended') {
    alert('⚠️ Shop của bạn hiện đang bị đình chỉ. Một số chức năng như thêm, sửa, xóa sản phẩm hoặc voucher đã bị giới hạn.');

    // 🧩 Hàm tiện ích chung
    const blockClick = (selector, message) => {
        document.querySelectorAll(selector).forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert(message);
            }, { capture: true });
        });
    };
    const blockSubmit = (selector, message) => {
        const form = document.querySelector(selector);
        if (form) {
            form.addEventListener('submit', e => {
                e.preventDefault();
                e.stopImmediatePropagation();
                alert(message);
            }, { capture: true });
        }
    };

    // 🛍️ 1️⃣ Khóa sản phẩm (thêm, sửa, xóa)
    const disableProducts = () => {
        blockClick('.btn-edit', '🚫 Shop đang bị đình chỉ — không thể chỉnh sửa sản phẩm.');
        blockClick('.btn-delete-product', '🚫 Shop đang bị đình chỉ — không thể xóa sản phẩm.');
        blockSubmit('.product-form[action*="products/store"]', '🚫 Shop đang bị đình chỉ — không thể thêm sản phẩm mới.');
    };

    // 🎫 2️⃣ Khóa voucher (thêm, sửa, xóa)
    const disableVouchers = () => {
        blockClick('.btn-edit-voucher', '🚫 Shop đang bị đình chỉ — không thể chỉnh sửa voucher.');
        blockClick('.btn-delete', '🚫 Shop đang bị đình chỉ — không thể xóa voucher.');
        blockSubmit('#voucherAddForm', '🚫 Shop đang bị đình chỉ — không thể thêm voucher mới.');
    };

    // 👤 3️⃣ Khóa tài khoản cá nhân
    const disableAccount = () => {
        blockSubmit('#formInfo', '🚫 Shop đang bị đình chỉ — không thể thay đổi thông tin cá nhân.');
        blockSubmit('#formPassword', '🚫 Shop đang bị đình chỉ — không thể đổi mật khẩu.');
    };

    // 🏪 4️⃣ Khóa form shop
    const disableShopForm = () => {
        blockSubmit('#formShop', '🚫 Shop đang bị đình chỉ — không thể cập nhật thông tin shop.');
    };

    // 🔁 5️⃣ Gọi lại mỗi khi chuyển tab (vì nội dung render động)
    const origNavigate = window.navigate;
    window.navigate = function(view) {
        origNavigate(view);
        setTimeout(() => {
            disableProducts();
            disableVouchers();
            disableAccount();
            disableShopForm();
        }, 800);
    };

    // 🚀 6️⃣ Gọi khi mới vào dashboard
    disableProducts();
    disableVouchers();
    disableAccount();
    disableShopForm();
}

// 🚫 Chặn riêng nút "Thêm sản phẩm" và "Thêm voucher" khi shop bị đình chỉ
document.addEventListener('click', function(e) {
    if (shopStatus === 'suspended') {
        if (e.target.closest('a[href*="/seller/products/create"]') || e.target.closest('button.add-product-btn')) {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể thêm sản phẩm mới.');
            return;
        }

        if (e.target.closest('a[href*="/seller/vouchers/create"]') || e.target.closest('button.add-voucher-btn')) {
            e.preventDefault();
            alert('🚫 Shop của bạn đang bị đình chỉ — không thể thêm voucher mới.');
            return;
        }
    }
});

// 🟦 Lọc theo trạng thái (đặt trong phạm vi global, không lỗi biến)
// 🟦 Lọc theo trạng thái (chính xác theo data-status)
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const grid = document.getElementById('productsGrid');

    if (statusFilter && grid) {
        statusFilter.addEventListener('change', function() {
            const selected = statusFilter.value.trim().toLowerCase();

            grid.querySelectorAll('.card.product-card').forEach(function(card) {
                const status = (card.dataset.status || '').toLowerCase();

                if (!selected || status === selected) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
}

</script>

</body>
</html>