<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của tôi - E-Market</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background:#f5f5f5; font-family: 'Inter', sans-serif; }
        .cart-container { max-width: 1000px; margin: 30px auto; }
        .shop-box { background:#fff; margin-bottom:20px; padding:15px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        .shop-title { font-size:18px; font-weight:600; margin-bottom:12px; color:#ee4d2d; }
        .cart-item { display:flex; align-items:center; gap:16px; padding:12px 0; border-bottom:1px solid #eee; }
        .cart-item img { width:80px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
        .cart-info { flex:1; }
        .cart-actions { text-align:right; }
        .quantity-control { display:flex; align-items:center; gap:5px; margin-top:5px; }
        .qty-label { font-size:15px; font-weight:500; color:#333; }
        .qty-input { width:50px; height:32px; text-align:center; border:1px solid #ccc; border-radius:4px; background:#fff; }
        .subtotal { font-size:14px; color:#444; margin-top:5px; }
        .total-box { text-align:right; margin-top:20px; font-size:18px; font-weight:600; }
        .btn-checkout { background:#ee4d2d; color:#fff; font-weight:600; padding:10px 20px; border:none; border-radius:6px; }
        .btn-checkout:hover { background:#c2410c; }
        .btn-checkout:disabled { background:#ccc; cursor:not-allowed; }
        .back-btn { position:fixed; top:20px; left:20px; background:#ee4d2d; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:600; z-index:1000; }
        .back-btn:hover { background:#c2410c; }
    </style>
</head>
<body>

<a href="javascript:history.back()" class="back-btn">← Quay lại</a>

<div class="cart-container">
    <h2>🛒 Giỏ hàng của tôi</h2>

    @if($cart->items->isEmpty())
        <p>Giỏ hàng của bạn đang trống.</p>
    @else
        <form id="checkout-form" action="{{ route('checkout.fromCart') }}" method="POST">
            @csrf
            @php
                $grouped = $cart->items
                    ->groupBy(fn($item) => $item->product->seller->shop->name ?? 'Không có Shop')
                    ->sortKeys();
            @endphp

            @foreach($grouped as $shopName => $items)
                <div class="shop-box">
                    <div class="shop-title">{{ $shopName }}</div>
                    @foreach($items as $item)
                        @php
                            $subtotal = $item->product->price * $item->quantity;
                            $img = count($item->product->images ?? [])
                                ? Storage::disk('public')->url($item->product->images[0])
                                : '/Picture/products/Aothun.jpg';
                        @endphp
                        <div class="cart-item" data-id="{{ $item->id }}">
                            <input type="checkbox" class="item-checkbox" name="items[]" 
                                   value="{{ $item->product->id }}" 
                                   data-price="{{ $item->product->price }}" 
                                   data-qty="{{ $item->quantity }}">
                            <img src="{{ $img }}" alt="{{ $item->product->name }}">
                            <div class="cart-info">
                                <div><strong>{{ $item->product->name }}</strong></div>
                                <div>Giá: {{ number_format($item->product->price, 0, ',', '.') }} đ</div>
                                <div class="quantity-control">
                                    <span class="qty-label">Số lượng:</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary qty-minus">-</button>
                                    <input type="number" class="qty-input" min="1" value="{{ $item->quantity }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary qty-plus">+</button>
                                </div>
                                <div class="subtotal">Tổng: <span class="subtotal-value">{{ number_format($subtotal, 0, ',', '.') }}</span> đ</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="total-box">
                Tổng cộng: <span id="total-price">0</span> đ
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn-checkout" id="checkout-btn">Tiến hành thanh toán</button>
            </div>
        </form>
    @endif
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const checkboxes = document.querySelectorAll(".item-checkbox");
    const totalPriceEl = document.getElementById("total-price");
    const checkoutBtn = document.getElementById("checkout-btn");

    function updateTotal() {
        let total = 0, hasSelected = false;
        checkboxes.forEach(cb => {
            const item = cb.closest(".cart-item");
            const qty = parseInt(item.querySelector(".qty-input").value);
            const price = parseFloat(cb.dataset.price);
            if (cb.checked) {
                total += qty * price;
                hasSelected = true;
            }
        });
        totalPriceEl.textContent = total.toLocaleString("vi-VN");
        checkoutBtn.disabled = !hasSelected;
    }

    // ✅ Nút + và -
    document.querySelectorAll(".qty-minus").forEach(btn => {
        btn.addEventListener("click", () => {
            const input = btn.parentElement.querySelector(".qty-input");
            let val = Math.max(1, parseInt(input.value) - 1);
            input.value = val;
            updateSubtotal(btn.closest(".cart-item"));
            updateTotal();
            saveQuantity(btn.closest(".cart-item"));
        });
    });

    document.querySelectorAll(".qty-plus").forEach(btn => {
        btn.addEventListener("click", () => {
            const input = btn.parentElement.querySelector(".qty-input");
            input.value = parseInt(input.value) + 1;
            updateSubtotal(btn.closest(".cart-item"));
            updateTotal();
            saveQuantity(btn.closest(".cart-item"));
        });
    });

    // ✅ Cập nhật subtotal mỗi item
    function updateSubtotal(item) {
        const price = parseFloat(item.querySelector(".item-checkbox").dataset.price);
        const qty = parseInt(item.querySelector(".qty-input").value);
        const subtotal = price * qty;
        item.querySelector(".subtotal-value").textContent = subtotal.toLocaleString("vi-VN");
        item.querySelector(".item-checkbox").dataset.qty = qty;
    }

    // ✅ Gửi AJAX cập nhật DB
    function saveQuantity(item) {
        const id = item.dataset.id;
        const qty = item.querySelector(".qty-input").value;
        fetch(`/cart/update/${id}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ quantity: qty })
        });
    }

    // ✅ Khi chọn / bỏ chọn
    checkboxes.forEach(cb => cb.addEventListener("change", updateTotal));
    updateTotal();
});
</script>
</body>
</html>
