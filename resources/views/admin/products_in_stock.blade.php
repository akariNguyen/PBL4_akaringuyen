@extends('admin.admin')

@push('styles')
<style>
.admin-products-page {
  --border: #e5e7eb;
  --muted: #6b7280;
  --primary: #16a34a;
  --hover: #15803d;
  --danger: #dc2626;
}

/* ===== Layout chính ===== */
.admin-products-page .product-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 20px;
  align-items: start;
  min-height: calc(100vh - 140px);
}

/* ===== Sidebar danh mục ===== */
.admin-products-page .sidebar {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.admin-products-page .sidebar h5 {
  font-weight: 700;
  font-size: 16px;
  margin-bottom: 16px;
}
.admin-products-page .sidebar a {
  display: block;
  padding: 10px 14px;
  border-radius: 8px;
  color: #111827;
  text-decoration: none;
  background: #f9fafb;
  border: 1px solid var(--border);
  transition: 0.2s;
}
.admin-products-page .sidebar a:hover {
  background: #f3f4f6;
}
.admin-products-page .sidebar a.active {
  background: #e0e7ff;
  color: #1d4ed8;
  font-weight: 600;
}

/* ===== Khu vực sản phẩm ===== */
.admin-products-page .product-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.admin-products-page .product-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.admin-products-page .product-header h1 {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
}

/* ===== Grid sản phẩm ===== */
.admin-products-page .grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

/* ===== Card sản phẩm ===== */
.admin-products-page .card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  transition: 0.2s ease-in-out;
}
.admin-products-page .card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}
.admin-products-page .card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}
.admin-products-page .card-body {
  padding: 12px 14px;
}
.admin-products-page .name {
  font-weight: 600;
  font-size: 15px;
  margin-bottom: 4px;
  color: #111827;
}
.admin-products-page .info-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 4px;
}
.admin-products-page .price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.admin-products-page .price {
  color: var(--danger);
  font-weight: 700;
  font-size: 15px;
}
.admin-products-page .sold {
  font-size: 13px;
  color: var(--muted);
}
.admin-products-page .btn-buy {
  display: block;
  width: 100%;
  text-align: center;
  background: var(--primary);
  color: #fff;
  border-radius: 6px;
  font-weight: 600;
  padding: 8px 0;
  text-decoration: none;
  transition: background 0.2s;
  margin-top: 6px;
}
.admin-products-page .btn-buy:hover {
  background: var(--hover);
}

/* ===== Modal Popup ===== */
.modal-content {
  border-radius: 12px;
}
.modal-body {
  background: #f9fafb;
}
.product-popup-card {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid #eee;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.product-popup-card img {
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 12px;
  border: 1px solid #ddd;
}
.shop-info {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
  border-top: 1px solid #eee;
  padding-top: 12px;
}
.shop-info img {
  width: 90px;
  height: 90px;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid #ddd;
}
.shop-details p {
  margin: 2px 0;
  font-size: 14px;
  color: #333;
}
.review-item {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 8px 10px;
  background: #fafafa;
  margin-bottom: 8px;
}
.review-item small {
  color: #777;
}
</style>
@endpush

@section('content')
<div class="container-fluid admin-products-page">
  <div class="product-layout">
    <!-- 🧭 Sidebar -->
    <aside class="sidebar">
      <h5>Danh mục sản phẩm</h5>
      <ul>
        <li>
          <a href="{{ route('admin.products.inStock') }}" class="{{ !request('category') ? 'active' : '' }}">
            Tất cả sản phẩm
          </a>
        </li>
        @foreach($categories as $category)
          <li>
            <a href="{{ route('admin.products.inStock', ['category' => $category->id]) }}"
               class="{{ request('category') == $category->id ? 'active' : '' }}">
               {{ $category->name }}
            </a>
          </li>
        @endforeach
      </ul>
    </aside>

    <!-- 🎴 Danh sách sản phẩm -->
    <section class="product-section">
      <div class="product-header">
        <h1>📦 Danh sách sản phẩm đang bán</h1>
        <form action="{{ route('admin.products.inStock') }}" method="GET" class="d-flex align-items-center" style="gap:8px;">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control"
              placeholder="🔍 Tìm theo sản phẩm hoặc shop..."
              style="width:280px; border-radius:8px; border:1px solid #d1d5db; padding:8px 10px;">
          <button type="submit" class="btn btn-success" style="padding:8px 14px; border-radius:8px;">
            Tìm kiếm
          </button>
        </form>
      </div>

      @if($products->isEmpty())
        <div class="empty">Không có sản phẩm nào đang bán.</div>
      @else
        <div class="grid">
          @foreach($products as $p)
            @php
              $imgs = is_array($p->images) ? $p->images : json_decode($p->images, true);
              $img = (is_array($imgs) && count($imgs))
                ? Storage::disk('public')->url($imgs[0])
                : '/Picture/products/Aothun.jpg';
              $shop = \App\Models\Shop::where('user_id', $p->seller_id)->with('user')->first();
              $supplier = $shop ? $shop->name : ($p->seller->name ?? 'Nhà cung cấp');
              $avgRating = round($p->reviews()->avg('rating') ?? 0, 1);
              $soldCount = \App\Models\OrderItem::where('product_id', $p->id)->sum('quantity');
            @endphp

            <div class="card">
              <img src="{{ $img }}" alt="{{ $p->name }}">
              <div class="card-body">
                <div class="name">{{ $p->name }}</div>
                <div class="info-row">
                  <span>{{ $supplier }}</span>
                  <span>⭐ {{ $avgRating }}</span>
                </div>
                <div class="price-row">
                  <span class="price">{{ number_format($p->price, 0, ',', '.') }} đ</span>
                  <span class="sold">Đã bán: {{ $soldCount }}</span>
                </div>
                <button type="button" class="btn-buy" data-bs-toggle="modal" data-bs-target="#productModal{{ $p->id }}">
                  Xem thêm
                </button>
              </div>
            </div>

            <!-- ✅ Popup -->
            <!-- ✅ Popup -->
<div class="modal fade" id="productModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">{{ $p->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- ✅ CARD CHỨA TOÀN BỘ NỘI DUNG -->
        <div class="product-popup-card">

          <!-- 🔹 Ảnh sản phẩm -->
          <div class="popup-image-wrapper" style="
              display: flex;
              justify-content: center;
              align-items: center;
              background: #f9fafb;
              border: 1px solid #eee;
              border-radius: 12px;
              height: 360px;
              margin-bottom: 18px;
              overflow: hidden;">
            <img src="{{ $img }}" alt="{{ $p->name }}" style="
                max-height: 100%;
                max-width: 100%;
                object-fit: contain;
                border-radius: 8px;">
          </div>

          <!-- 🔸 Thông tin Shop -->
          @if($shop)
          <div class="shop-info" style="
              display: flex;
              gap: 16px;
              margin-bottom: 16px;
              border-top: 1px solid #eee;
              padding-top: 12px;">
            <img src="{{ $shop->logo_path ? asset('storage/'.$shop->logo_path) : asset('images/default_shop.png') }}"
                 alt="Logo"
                 style="width: 90px; height: 90px; border-radius: 8px; object-fit: cover; border: 1px solid #ddd;">
            <div class="shop-details" style="font-size: 14px;">
              <h6 class="fw-bold">{{ $shop->name }}</h6>
              <p><strong>Chủ shop:</strong> {{ $shop->user->name ?? 'Chưa có' }}</p>
              <p><strong>Email:</strong> {{ $shop->user->email ?? 'Chưa có' }}</p>
              <p><strong>Mô tả:</strong> {{ $shop->description ?? 'Không có mô tả' }}</p>
            </div>
          </div>
          @endif

          <!-- 💬 Bình luận -->
          <div class="review-card" style="
              margin-top: 12px;
              background: #fff;
              border-radius: 12px;
              padding: 16px;
              border: 1px solid #eee;">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold mb-0">💬 Bình luận sản phẩm</h6>
              <select class="form-select" id="filterSelect{{ $p->id }}" style="width:150px; font-size:14px;">
                <option value="all" selected>Tất cả</option>
                @for($i=5;$i>=1;$i--)
                  <option value="{{ $i }}">{{ $i }} sao</option>
                @endfor
              </select>
            </div>

            @php
              $reviews = \App\Models\Review::where('product_id', $p->id)
                  ->latest()
                  ->with('user')
                  ->get();
            @endphp
            <div id="reviewList{{ $p->id }}">
              @if($reviews->isEmpty())
                <p class="text-muted mb-0">Chưa có bình luận nào cho sản phẩm này.</p>
              @else
                @foreach($reviews as $r)
                  <div class="review-item" data-rating="{{ $r->rating }}" style="
                      border: 1px solid #eee;
                      border-radius: 8px;
                      padding: 10px;
                      background: #fafafa;
                      margin-bottom: 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                      <strong>{{ $r->user->name ?? 'Người dùng' }}</strong>
                      <div>
                        @for($i=1; $i<=5; $i++)
                          <span class="star {{ $i <= $r->rating ? 'filled' : '' }}" 
                                style="font-size:16px; color:{{ $i <= $r->rating ? '#facc15' : '#d1d5db' }}">★</span>
                        @endfor
                      </div>
                    </div>
                    <div>{{ $r->comment ?: 'Không có nội dung bình luận.' }}</div>
                    <small style="color:#777;">{{ $r->created_at->diffForHumans() }}</small>
                  </div>
                @endforeach
              @endif
            </div>
          </div> <!-- end review-card -->
        </div> <!-- end product-popup-card -->
      </div> <!-- end modal-body -->

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const select = document.getElementById('filterSelect{{ $p->id }}');
  if (select) {
    select.addEventListener('change', () => {
      const value = select.value;
      const reviews = document.querySelectorAll('#reviewList{{ $p->id }} .review-item');
      reviews.forEach(r => {
        const rating = r.getAttribute('data-rating');
        r.style.display = (value === 'all' || value === rating) ? 'block' : 'none';
      });
    });
  }
});
</script>
          @endforeach
        </div>
      @endif
    </section>
  </div>
</div>
@endsection
