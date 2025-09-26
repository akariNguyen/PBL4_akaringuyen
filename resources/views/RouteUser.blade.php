<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E‑Market</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#111827; --muted:#6b7280; --border:#e5e7eb; --brand:#2563eb; --bg:#f8fafc; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Inter', sans-serif; background:var(--bg); color:#111827; }
        .topbar { height:64px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:16px; padding:0 16px; position:sticky; top:0; background:#fff; z-index:50; }
        .brand { display:flex; align-items:center; gap:10px; font-weight:700; font-size:22px; }
        .brand img { height:80px; width:auto; }
        .search { flex:1; display:flex; gap:8px; }
        .search input { flex:1; padding:10px 12px; border:1px solid var(--border); border-radius:8px; background:#fff; }
        .search button { padding:10px 14px; border-radius:8px; background:var(--brand); color:#fff; border:1px solid var(--brand); cursor:pointer; }
        .nav-actions { display:flex; align-items:center; gap:12px; margin-left: 50px; } /* Dịch sang trái 50px */
        .nav-actions a { text-decoration:none; padding:8px 12px; border:1px solid var(--border); border-radius:8px; color:#111827; background:#fff; }
        .nav-actions a:hover { background:#f3f4f6; }
        .dropdown { position:relative; }
        .dropdown-content { display:none; position:absolute; right:0; background:#fff; border:1px solid var(--border); border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.04); min-width:160px; z-index:100; }
        .dropdown:hover .dropdown-content { display:block; }
        .dropdown-content a { display:block; padding:8px 12px; text-decoration:none; color:#111827; }
        .dropdown-content a:hover { background:#f3f4f6; }
        .benefits { display:flex; align-items:center; gap:24px; padding:8px 16px; border-bottom:1px solid var(--border); background:#fff; }
        .benefit { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:14px; }
        .benefit .dot { width:6px; height:6px; background:#7c3aed; border-radius:999px; display:inline-block; }
        .page { margin:0; padding:0; }
        .layout { display:grid; grid-template-columns: 260px 1fr; min-height: calc(100vh - 96px); gap:16px; }
        .panel { background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
        .sidebar { padding:12px; align-self:stretch; }
        .side-title { font-weight:700; margin-bottom:8px; }
        .menu { list-style:none; padding:0; margin:0; }
        .menu li { margin:6px 0; }
        .menu a { text-decoration:none; color:#111827; padding:10px 12px; display:block; border-radius:8px; border:1px solid var(--border); background:#fff; }
        .menu a.active, .menu a:hover { background:#f3f4f6; }
        .content { padding:16px; }
        .grid { display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; }
        .card { border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,0.04); }
        .card img { width:100%; height:160px; object-fit:cover; display:block; background:#f3f4f6; }
        .card-body { padding:12px; }
        .name { font-weight:600; margin-bottom:6px; }
        .price { color:#ef4444; font-weight:700; }
        .seller { color:var(--muted); font-size:13px; margin-top:4px; }
        .empty { text-align:center; color:var(--muted); padding:40px 16px; border:1px dashed var(--border); border-radius:12px; background:#fff; }
        @media (max-width: 1200px) { .grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } .grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 520px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php
        $q = request('q');
        $categoryId = request('c');
        $categories = \App\Models\Category::orderBy('name')->get();
        $productsQuery = \App\Models\Product::query()->with(['seller','category']);
        if ($q) { $productsQuery->where('name', 'like', '%'.$q.'%'); }
        if ($categoryId) { $productsQuery->where('category_id', $categoryId); }
        $products = $productsQuery->latest()->take(40)->get();
    ?>

    <div class="topbar">
        <div class="brand">
            <img src="/Picture/logo.png" alt="E‑Market">
            <span style="color:#2563eb;">E‑Market</span>
        </div>
        <form class="search" method="get" action="">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit">Tìm kiếm</button>
        </form>
        <div class="nav-actions">
            <a href="/">Trang chủ</a>
            <div class="dropdown">
                <a href="#" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; color:#111827; background:#fff;">Tài khoản</a>
                <div class="dropdown-content">
                    <a href="{{ route('account.personal') }}">Thông tin cá nhân</a>
                    <a href="{{ route('orders.my') }}">Đơn hàng của tôi</a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="width:100%; text-align:left; background:none; border:none; cursor:pointer; padding:8px 12px; color:#111827;">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="benefits">
        <span class="benefit"><span class="dot"></span> Cam kết 100% hàng thật</span>
        <span class="benefit"><span class="dot"></span> Freeship mọi đơn</span>
        <span class="benefit"><span class="dot"></span> Hoàn 200% nếu hàng giả</span>
        <span class="benefit"><span class="dot"></span> 30 ngày đổi trả</span>
        <span class="benefit"><span class="dot"></span> Giao nhanh 2h</span>
        <span class="benefit"><span class="dot"></span> Giá siêu rẻ</span>
    </div>

    <div class="page">
        <div class="layout">
            <aside class="panel sidebar">
                <div class="side-title">Danh mục</div>
                <ul class="menu">
                    <li><a href="{{ url()->current() }}" class="{{ request('c') ? '' : 'active' }}">Tất cả sản phẩm</a></li>
                    @foreach($categories as $cat)
                        <li><a href="{{ url()->current() . '?c=' . $cat->id }}" class="{{ request('c') == $cat->id ? 'active' : '' }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </aside>
            <main class="panel content">
                @if($products->isEmpty())
                    <div class="empty">Chưa có sản phẩm để hiển thị.</div>
                @else
                    <div class="grid">
                        @foreach($products as $p)
                            <?php
                                $imgs = is_array($p->images) ? $p->images : [];
                                $img = count($imgs) ? Storage::disk('public')->url($imgs[0]) : '/Picture/products/Aothun.jpg';
                                $shop = \App\Models\Shop::find($p->seller_id);
                                $supplier = $shop ? $shop->name : ($p->seller->name ?? 'Nhà cung cấp');
                            ?>
                            <div class="card">
                                <img src="{{ $img }}" alt="{{ $p->name }}">
                                <div class="card-body">
                                    <div class="name">{{ $p->name }}</div>
                                    <div class="price">{{ number_format($p->price, 0, ',', '.') }} đ</div>
                                    <div class="seller">{{ $supplier }}</div>
                                    <a href="{{ route('product.show', $p->id) }}" style="display:block; margin-top:8px; padding:8px 12px; border:none; border-radius:6px; background:#16a34a; color:#fff; font-weight:600; cursor:pointer; text-decoration:none;">
                                        Đặt hàng
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>
</body>
</html>