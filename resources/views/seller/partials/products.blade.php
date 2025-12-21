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
@php
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
@endphp
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



