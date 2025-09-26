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
        .container { max-width: 1200px; margin: 20px auto; display: flex; gap: 20px; }
        .images { flex: 1; }
        .images img { width: 100%; border-radius: 4px; }
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
    </style>
</head>
<body>
    <div class="container">
        <div class="images">
            @foreach($product->images ?? [] as $img)
                <img src="{{ Storage::disk('public')->url($img) }}" alt="{{ $product->name }}">
            @endforeach
        </div>
        <div class="details">
            <div class="title">{{ $product->name }}</div>
            <div class="rating">
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($avgRating))
                            ★
                        @else
                            ☆
                        @endif
                    @endfor
                </div>
                <span class="reviews">{{ number_format($product->reviews->count()) }} đánh giá</span>
                <span class="reviews">Đã Bán {{ number_format($product->sold_quantity ?? 0, 0, ',', '.') }}k</span>
            </div>
            <div class="price">đ{{ number_format($product->price, 0, ',', '.') }}</div>
            <div class="shipping">Vận Chuyển: Nhận từ {{ $startShippingDate }} - {{ $endShippingDate }}, phí giao đ0</div>
            <div class="voucher">Tặng Voucher đ15.000 nếu đơn giá sau thời gian trên.</div>
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
                <span>({{ $product->quantity - ($product->sold_quantity ?? 0) }} sản phẩm có sẵn)</span>
            </div>
            <div class="actions">
                <button class="add-cart">Thêm Vào Giỏ Hàng</button>
                <form action="{{ route('checkout', $product->id) }}" method="GET" id="buy-now-form" style="display:inline;">
                    <input type="hidden" name="quantity" id="hidden-quantity" value="1">
                    <button type="submit" class="buy-now" onclick="return confirmCheckout()">Mua Ngay</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ĐÁNH GIÁ -->
    <div class="review-section">
        <h2>Đánh giá sản phẩm</h2>

        @auth
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
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
        @endif
    </div>

    <script>
        // Chọn biến thể
        document.querySelectorAll('.var-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.var-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
            });
        });

        // Tăng giảm số lượng
        const qtyInput = document.querySelector('#quantity');
        const hiddenQty = document.querySelector('#hidden-quantity');
        const maxStock = {{ $product->quantity - ($product->sold_quantity ?? 0) }};
        document.querySelector('.qty-btn.minus').addEventListener('click', () => {
            let value = parseInt(qtyInput.value);
            if (value > 1) {
                qtyInput.value = value - 1;
                hiddenQty.value = value - 1;
            }
        });
        document.querySelector('.qty-btn.plus').addEventListener('click', () => {
            let value = parseInt(qtyInput.value);
            if (value < maxStock) {
                qtyInput.value = value + 1;
                hiddenQty.value = value + 1;
            } else {
                alert('Số lượng vượt quá tồn kho!');
            }
        });

        // Thêm giỏ hàng
        document.querySelector('.add-cart').addEventListener('click', () => {
            alert('Đã thêm vào giỏ hàng!');
        });

        // Xác nhận mua
        function confirmCheckout() {
            const quantity = parseInt(document.querySelector('#quantity').value);
            const totalPrice = {{ $product->price }} * quantity;
            if (confirm('Bạn có chắc chắn muốn đặt hàng ' + quantity + ' sản phẩm với giá đ' + totalPrice.toLocaleString('vi-VN') + ' không?')) {
                document.querySelector('#hidden-quantity').value = quantity;
                return true;
            }
            return false;
        }

        // Hiển thị sao khi chọn
        document.querySelectorAll('input[name="rating"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.star').forEach((star, idx) => {
                    star.textContent = (idx < radio.value) ? '★' : '☆';
                });
            });
        });
    </script>
</body>
</html>
