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
        .qty-input {
            width:35px; height: 32px;
            text-align:center; font-size:14px; color:#666;
            border:1px solid #ccc; border-radius:4px; background:#f9f9f9;
        }
        .qty-input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 3px rgba(37,99,235,0.5); }
        .subtotal { font-size:14px; color:#444; margin-top:5px; }
        .total-box { text-align:right; margin-top:20px; font-size:18px; font-weight:600; }
        .btn-checkout { background:#ee4d2d; color:#fff; font-weight:600; padding:10px 20px; border:none; border-radius:6px; }
        .btn-checkout:hover { background:#c2410c; }
        .btn-checkout:disabled { background:#ccc; cursor:not-allowed; }

        /* 🔥 Nút quay lại */
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #ee4d2d;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: none;
            transition: background 0.2s;
            z-index: 1000;
        }
        .back-btn:hover { background: #c2410c; }
        
        /* Thông báo lỗi */
        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>

<!-- Nút quay lại -->
<a href="javascript:history.back()" class="back-btn">← Quay lại</a>

<div class="cart-container">
    <h2>🛒 Giỏ hàng của tôi</h2>

    <!-- Hiển thị lỗi từ server -->
    @if ($errors->has('cart'))
        <div class="error-message">
            {{ $errors->first('cart') }}
        </div>
    @endif

    @if($cart->items->isEmpty())
        <p>Giỏ hàng của bạn đang trống.</p>
    @else
        <!-- ✅ Form gửi sang checkout.fromCart -->
        <form id="checkout-form" action="{{ route('checkout.fromCart') }}" method="POST">
            @csrf

            @php
                // Nhóm items theo shop, sắp xếp tên shop A->Z
                $grouped = $cart->items->groupBy(fn($item) => $item->product->seller->shop->name ?? 'Không có Shop')
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
                        <div class="cart-item">
                            <!-- Checkbox -->
                            <input type="checkbox" class="item-checkbox" name="items[]" 
                                value="{{ $item->product->id }}" 
                                data-price="{{ $item->product->price }}" 
                                data-qty="{{ $item->quantity }}">

                            <!-- Ảnh sản phẩm -->
                            <img src="{{ $img }}" alt="{{ $item->product->name }}">

                            <!-- Thông tin -->
                            <div class="cart-info">
                                <div><strong>{{ $item->product->name }}</strong></div>
                                <div>Giá: {{ number_format($item->product->price, 0, ',', '.') }} đ</div>
                                <div class="quantity-control">
                                    <span class="qty-label">Số lượng:</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary qty-minus">-</button>
                                    <input type="text" class="qty-input" 
                                        value="{{ $item->quantity }}" readonly 
                                        data-item-id="{{ $item->id }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary qty-plus">+</button>
                                </div>
                                <div class="subtotal">Tổng: {{ number_format($subtotal, 0, ',', '.') }} đ</div>
                            </div>

                            <!-- Xóa -->
                            <div class="cart-actions">
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="document.getElementById('delete-item-{{ $item->id }}').submit();">
                                    Xóa
                                </button>
                            </div>
                        </div>

                        <!-- ✅ form xóa ẩn (tách riêng, không lồng trong checkout form) -->
                        <form id="delete-item-{{ $item->id }}" 
                              action="{{ route('cart.remove', $item->id) }}" 
                              method="POST" style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
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
    console.log('✅ Cart page loaded');
    const form = document.getElementById("checkout-form");
    const checkboxes = document.querySelectorAll(".item-checkbox");
    const totalPriceEl = document.getElementById("total-price");
    const checkoutBtn = document.getElementById("checkout-btn");

    function updateTotal() {
        let total = 0;
        let hasSelectedItems = false;
        document.querySelectorAll(".cart-item").forEach(item => {
            const cb = item.querySelector(".item-checkbox");
            const qtyInput = item.querySelector(".qty-input");
            const price = parseFloat(cb.dataset.price);
            if (cb.checked) {
                hasSelectedItems = true;
                let qty = parseInt(qtyInput.value);
                total += price * qty;
            }
        });
        totalPriceEl.textContent = total.toLocaleString("vi-VN");
        checkoutBtn.disabled = !hasSelectedItems;
    }

    // Tăng/giảm số lượng
    document.querySelectorAll(".qty-minus").forEach(btn => {
        btn.addEventListener("click", () => {
            const input = btn.nextElementSibling;
            if (input.value > 1) {
                input.value--;
                updateSubtotal(input);
                updateTotal();
            }
        });
    });
    document.querySelectorAll(".qty-plus").forEach(btn => {
        btn.addEventListener("click", () => {
            const input = btn.previousElementSibling;
            input.value++;
            updateSubtotal(input);
            updateTotal();
        });
    });

    function updateSubtotal(input) {
        const item = input.closest(".cart-item");
        const cb = item.querySelector(".item-checkbox");
        const price = parseFloat(cb.dataset.price);
        const qty = parseInt(input.value);
        const subtotalEl = item.querySelector(".subtotal");
        subtotalEl.textContent = "Tổng: " + (price * qty).toLocaleString("vi-VN") + " đ";
        cb.dataset.qty = qty;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateTotal);
        if (!cb.hasAttribute('name')) {
            cb.setAttribute('name', 'items[]');
        }
    });

    // Chỉ validate trong form submit
    form.addEventListener("submit", function(e) {
        const checkedItems = Array.from(checkboxes).filter(cb => cb.checked);
        console.log('Checked items:', checkedItems.length);
        if (checkedItems.length === 0) {
            e.preventDefault();
            alert('❌ Vui lòng chọn ít nhất một sản phẩm để thanh toán!');
            return false;
        }
        console.log('✅ Proceeding to checkout...');
        // Không return false ở đây!
    });

    updateTotal();
});
</script>
    
 
</body>
</html>