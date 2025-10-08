<?php
// Tính ngày giao hàng
$currentDate = new DateTime();
$startShippingDate = (clone $currentDate)->modify('+3 days')->format('d M');
$endShippingDate = (clone $currentDate)->modify('+5 days')->format('d M');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - E-Market</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ee4d2d; --text: #333; --muted: #999; --bg: #f5f5f5; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); color: var(--text); }

        /* Căn giữa toàn bộ khung */
        .container-wrapper { display: flex; justify-content: center; margin-top: 20px; }
        .container {
            max-width: 1000px; display: flex; gap: 20px;
            background: #fff; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .images { flex: 1; max-width: 350px; }
        .images img { width: 100%; border-radius: 8px; border: 1px solid #ddd; }
        .details { flex: 1; }
        .title { font-size: 24px; font-weight: 600; margin-bottom: 10px; }
        .rating { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .stars { color: #ffd700; }
        .reviews { color: var(--muted); }
        .price { font-size: 24px; color: var(--primary); font-weight: 600; margin-bottom: 10px; }
        .shipping { margin-bottom: 10px; color: var(--muted); }
        .voucher { margin-bottom: 20px; color: green; }
        .variations { margin-bottom: 20px; }
        .var-label { font-weight: 600; margin-bottom: 5px; }
        .var-buttons { display: flex; flex-wrap: wrap; gap: 10px; }
        .var-btn { padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; background: #fff; }
        .var-btn.selected { border-color: var(--primary); background: #fff5f5; }
        .quantity { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .qty-input { width: 50px; text-align: center; border: 1px solid #ddd; padding: 5px; }
        .actions { display: flex; gap: 10px; }
        .add-cart { padding: 10px 20px; background: #ffeee8; color: var(--primary); border: 1px solid var(--primary); border-radius: 4px; cursor: pointer; font-weight: 600; }
        .buy-now { padding: 10px 20px; background: var(--primary); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .review-section { max-width: 800px; margin: 40px auto; }
        .review-card { padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 12px; background: #fff; }
        .review-card .stars { font-size: 18px; color: #ffd700; }

        /* 🔥 Nút quay lại màu cam sát lề trái */
        .back-btn {
            position: fixed; top: 20px; left: 20px;
            background: var(--primary); color: #fff;
            padding: 8px 14px; border-radius: 6px;
            text-decoration: none; font-size: 14px; font-weight: 600;
            border: none; transition: background 0.2s; z-index: 1000;
        }
        .back-btn:hover { background: #c2410c; }

        /* Shop info */
        .shop-info {
            display: flex; align-items: flex-start; gap: 20px;
            max-width:780px; width: 100%;
            background: #fff; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .shop-banner img { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .shop-details { flex: 1; }
        .shop-name { font-size: 22px; font-weight: 700; margin: 0 0 12px; color: #333; }
        .shop-details p { margin: 6px 0; font-size: 15px; color: #444; }
    </style>
</head>
<body>
    <!-- Nút quay lại -->
    <a href="javascript:history.back()" class="back-btn">← Quay lại</a>

    <!-- Khung căn giữa -->
    <div class="container-wrapper">
        <div class="container">
            <div class="images">
                @foreach($product->images ?? [] as $img)
                    <img src="{{ Storage::disk('public')->url($img) }}" alt="{{ $product->name }}">
                @endforeach
            </div>

            <div class="details">
                {{-- Thông báo thêm giỏ hàng thành công --}}
                @if(session('success'))
                    <div style="background:#d1fae5; color:#065f46; padding:10px; border-radius:6px; margin-bottom:15px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="title">{{ $product->name }}</div>
                <div class="rating">
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($avgRating)) ★ @else ☆ @endif
                        @endfor
                    </div>
                    <span class="reviews">{{ number_format($product->reviews->count()) }} đánh giá</span>
                    <span class="reviews">Đã Bán {{ number_format($product->sold_quantity ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="price">đ{{ number_format($product->price, 0, ',', '.') }}</div>
            
                <div class="variations">
                    <div class="var-label">Tên</div>
                    <div class="var-buttons">
                        @foreach($variations ?? [] as $var)
                            <button class="var-btn">{{ $var }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="quantity">
                    <div>Số Lượng</div>
                    <button class="qty-btn minus">-</button>
                    <input class="qty-input" type="number" value="1" min="1" id="quantity">
                    <button class="qty-btn plus">+</button>
                    <span>({{ $product->quantity}} sản phẩm có sẵn)</span>
                </div>
                <div class="actions">
                    <!-- Form thêm giỏ hàng -->
                    <form action="{{ route('cart.add', $product->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="quantity" id="cart-quantity" value="1">
                        <button type="submit" class="add-cart">Thêm Vào Giỏ Hàng</button>
                    </form>

                    <!-- Form mua ngay -->
                    <form action="{{ route('checkout', $product->id) }}" method="GET" id="buy-now-form" style="display:inline;">
                        <input type="hidden" name="quantity" id="hidden-quantity" value="1">
                        <button type="submit" class="buy-now" onclick="return confirmCheckout()">Mua Ngay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin Shop -->
    @if($shop)
    <div class="container-wrapper">
        <div class="shop-info">
            <div class="shop-banner">
                <img src="{{ $shop->logo_path ? asset('storage/' . $shop->logo_path) : asset('images/default_shop.png') }}" alt="Logo Shop">
            </div>
            <div class="shop-details">
                <h3 class="shop-name">{{ $shop->name }}</h3>
                <p><strong>Chủ shop:</strong> {{ $shop->user->name }}</p>
                <p><strong>Số điện thoại:</strong> {{ $shop->user->phone ?? 'Chưa có' }}</p>
                <p><strong>Email:</strong> {{ $shop->user->email ?? 'Chưa có' }}</p>
                <p><strong>Mô tả:</strong> {{ $shop->description ?? 'Chưa có mô tả' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- PHẦN ĐÁNH GIÁ giữ nguyên -->
    <div class="review-section">
        <h2>Đánh giá sản phẩm</h2>

        @auth
            <!-- Form gửi đánh giá -->
            <form action="{{ route('reviews.store', $product->id) }}" method="POST" style="margin-bottom:20px;">
                @csrf
                <div style="margin-bottom:10px;">
                    <label>Chọn số sao:</label>
                    <div style="font-size:24px; color:#ffd700;">
                        @for($i=1; $i<=5; $i++)
                            <label style="cursor:pointer; margin-right:5px;">
                                <input type="radio" name="rating" value="{{ $i }}" style="display:none;" required>
                                <span class="star">☆</span>
                            </label>
                        @endfor
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label>Bình luận:</label><br>
                    <textarea name="comment" rows="3" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;" required></textarea>
                </div>
                <button type="submit" style="padding:8px 16px; background:#ee4d2d; color:#fff; border:none; border-radius:6px; cursor:pointer;">
                    Gửi đánh giá
                </button>
            </form>
        @else
            <p>Bạn cần <a href="{{ route('login') }}">đăng nhập</a> để đánh giá.</p>
        @endauth

        <!-- Form lọc bình luận -->
        <form method="GET" action="{{ route('product.show', $product->id) }}" style="margin-bottom:20px;">
            <label for="filter">Lọc bình luận:</label>
            <select name="filter" id="filter" onchange="this.form.submit()">
                <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Tất cả</option>
                <option value="newest" {{ $filter == 'newest' ? 'selected' : '' }}>Mới nhất</option>
                <option value="oldest" {{ $filter == 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                <option value="5stars" {{ $filter == '5stars' ? 'selected' : '' }}>5 sao</option>
                <option value="4stars" {{ $filter == '4stars' ? 'selected' : '' }}>4 sao</option>
                <option value="3stars" {{ $filter == '3stars' ? 'selected' : '' }}>3 sao</option>
                <option value="2stars" {{ $filter == '2stars' ? 'selected' : '' }}>2 sao</option>
                <option value="1star" {{ $filter == '1star' ? 'selected' : '' }}>1 sao</option>
            </select>
        </form>

        <!-- Hiển thị reviews -->
        @if($reviews->count())
            @foreach($reviews as $review)
                <div class="review-card">
                    <div style="font-weight:600;">{{ $review->user->name ?? 'Người dùng' }}</div>
                    <div class="stars">
                        @for($i=1; $i<=5; $i++)
                            {!! $i <= $review->rating ? '★' : '☆' !!}
                        @endfor
                    </div>
                    <div>{{ $review->comment }}</div>
                    <div style="color:#888; font-size:12px;">{{ $review->created_at->diffForHumans() }}</div>
                </div>
            @endforeach
        @else
            <p>Không có bình luận nào theo bộ lọc này.</p>
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Chọn biến thể
            document.querySelectorAll('.var-btn').forEach(btn =>
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.var-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                })
            );

            // Tăng giảm số lượng
            const qtyInput = document.querySelector('#quantity');
            const cartQty = document.querySelector('#cart-quantity');
            const hiddenQty = document.querySelector('#hidden-quantity');
            const maxStock = {{ $product->quantity - ($product->sold_quantity ?? 0) }};

            const updateQty = val => {
                if (val >= 1 && val <= maxStock) {
                    qtyInput.value = cartQty.value = hiddenQty.value = val;
                } else if (val > maxStock) {
                    alert('Số lượng vượt quá tồn kho!');
                }
            };

            document.querySelector('.qty-btn.minus').onclick = () => updateQty(+qtyInput.value - 1);
            document.querySelector('.qty-btn.plus').onclick  = () => updateQty(+qtyInput.value + 1);
            qtyInput.addEventListener('input', () => updateQty(+qtyInput.value));

            // Hiển thị sao khi chọn
            document.querySelectorAll('input[name="rating"]').forEach(radio =>
                radio.addEventListener('change', () => {
                    document.querySelectorAll('.star').forEach((star, i) => {
                        star.textContent = (i < radio.value) ? '★' : '☆';
                    });
                })
            );
        });

        // Xác nhận mua ngay
        function confirmCheckout() {
            const qty = +document.querySelector('#quantity').value;
            const total = {{ $product->price }} * qty;
            if (confirm(`Bạn có chắc chắn muốn đặt hàng ${qty} sản phẩm với giá đ${total.toLocaleString('vi-VN')} không?`)) {
                document.querySelector('#hidden-quantity').value = qty;
                return true;
            }
            return false;
        }
    </script>
</body>
</html>
