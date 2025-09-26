<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - E-Market</title>
    <style>
        body{font-family:Inter,Arial,sans-serif;margin:0;background:#f8fafc;color:#111827}
        .top{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#fff;border-bottom:1px solid #e5e7eb}
        .brand{display:flex;align-items:center;gap:10px;font-weight:700}
        .brand img{height:28px;width:auto}
        .container{max-width:1000px;margin:16px auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}

        /* Tabs */
        .tabs{display:flex;gap:12px;margin-bottom:16px}
        .tab-btn{padding:8px 16px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;cursor:pointer}
        .tab-btn.active{background:#e5e7eb;font-weight:600}

        .order{border:1px solid #e5e7eb;border-radius:12px;margin-bottom:12px}
        .order-header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb}
        .items{padding:12px}
        .item{display:grid;grid-template-columns:80px 1fr 120px 80px;gap:12px;align-items:center;border-top:1px dashed #e5e7eb;padding:10px 0}
        .item:first-child{border-top:0}
        .item img{width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb}
        .muted{color:#6b7280}
        .price{font-weight:700}
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="top">
        <div class="brand"><img src="/Picture/logo.png" alt="E-Market"> E-Market</div>
        <div><a href="/" style="text-decoration:none; padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; color:#111827; background:#fff;">Trang chủ</a></div>
    </div>

    <div class="container">
        <h2 style="margin:0 0 12px 0;">Đơn hàng của tôi</h2>

        <!-- Tabs -->
        <div class="tabs">
            <button onclick="showTab('pending')" class="tab-btn">Đang xử lý</button>
            <button onclick="showTab('shipping')" class="tab-btn">Đang giao hàng</button>
            <button onclick="showTab('completed')" class="tab-btn">Hoàn thành</button>
            <button onclick="showTab('cancelled')" class="tab-btn">Đã hủy</button>
        </div>

        <!-- Nội dung các tab -->
        @foreach (['pending'=>'Đang xử lý','shipping'=>'Đang giao hàng','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'] as $status => $label)
            <div class="tab-content" id="tab-{{ $status }}" style="display:none;">
                @php
                    $list = $orders->where('status', $status);
                @endphp

                @forelse($list as $order)
                    <div class="order">
                        <div class="order-header">
                            <div>
                                Mã đơn #{{ $order->id }} • 
                                <span class="muted">{{ $order->created_at->format('d/m/Y H:i') }}</span><br>
                                <span class="muted">Tình trạng: {{ $label }}</span><br>
                                <span class="muted">Dự kiến nhận: 
                                    {{ $order->created_at->copy()->addDays(3)->format('d/m') }} - 
                                    {{ $order->created_at->copy()->addDays(5)->format('d/m') }}
                                </span>
                            </div>
                            <div class="price">Tổng: {{ number_format($order->total_price, 0, ',', '.') }} đ</div>
                        </div>
                        <div class="items">
                            @foreach($order->items as $it)
                                <?php 
                                    $p = \App\Models\Product::find($it->product_id); 
                                    $img = $p && is_array($p->images) && count($p->images) 
                                        ? Storage::disk('public')->url($p->images[0]) 
                                        : '/Picture/products/Aothun.jpg'; 
                                ?>
                                <div class="item">
                                    <img src="{{ $img }}" alt="{{ $it->product_name }}">
                                    <div>
                                        <div style="font-weight:600">{{ $it->product_name }}</div>
                                        <div class="muted">Người bán: {{ \App\Models\Shop::find($it->seller_id)->name ?? '—' }}</div>
                                    </div>
                                    <div class="price">{{ number_format($it->price, 0, ',', '.') }} đ</div>
                                    <div class="muted">x{{ $it->quantity }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="muted">Không có đơn hàng {{ strtolower($label) }}.</p>
                @endforelse
            </div>
        @endforeach
    </div>

    <script>
        function showTab(status) {
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelector('#tab-' + status).style.display = 'block';

            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
        }
        // mở mặc định tab Pending
        showTab('pending');
    </script>
</body>
</html>
