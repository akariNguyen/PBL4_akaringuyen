<?php
// 🧮 Tính toán ngày giao hàng (3–5 ngày)
$currentDate = new DateTime();
$startShippingDate = (clone $currentDate)->modify('+3 days')->format('d M');
$endShippingDate = (clone $currentDate)->modify('+5 days')->format('d M');

// Dữ liệu cơ bản
$totalPrice = $product->price * ($cart['quantity'] ?? 1);
$shippingFee = 38000;
$finalTotal = $totalPrice + $shippingFee;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - E-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#ee4d2d; --text:#333; --muted:#999; --bg:#f5f5f5; }
        body { font-family:'Inter',sans-serif; margin:0; background:var(--bg); color:var(--text); }
        .container { max-width:800px; margin:20px auto; padding:25px; background:#fff; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
        h3 { color:var(--primary); margin-bottom:10px; }
        .address-section, .product-summary, .voucher-section, .total-section { margin-bottom:25px; }
        .address-input, .address-select, .voucher-select {
            width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; margin-bottom:10px;
        }
        .product-header, .product-item {
            display:grid; grid-template-columns:1fr 100px 150px; padding:10px 0;
            border-bottom:1px solid #eee; align-items:center;
        }
        .product-header { font-weight:600; color:var(--primary); border-bottom:2px solid var(--primary); }
        .shipping-info { color:green; margin-bottom:20px; }
        .total-row { display:flex; justify-content:space-between; margin:8px 0; }
        .total-row.total { font-weight:700; font-size:18px; border-top:1px solid #eee; padding-top:10px; }
        .place-order-btn {
            background:var(--primary); color:#fff; padding:12px 20px;
            border:none; border-radius:4px; width:100%; font-weight:600; cursor:pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="quantity" value="{{ $cart['quantity'] ?? 1 }}">

        {{-- Địa chỉ --}}
        <div class="address-section">
            <h3>Địa Chỉ Nhận Hàng</h3>
            @php
                $defaultText = $defaultAddress
                    ? ($defaultAddress->full_name.' - '.$defaultAddress->phone.' | '.$defaultAddress->address_line.' '.$defaultAddress->ward.' '.$defaultAddress->district.' '.$defaultAddress->city)
                    : '';
            @endphp
            <select name="address_id" class="address-select">
                <option value="">-- Chọn địa chỉ giao hàng --</option>
                @if($defaultAddress)
                    <option value="{{ $defaultAddress->id }}" selected> Mặc định: {{ $defaultText }}</option>
                @endif
                @foreach(($addresses ?? collect()) as $addr)
                    @if(!$defaultAddress || $addr->id !== $defaultAddress->id)
                        <option value="{{ $addr->id }}">
                            {{ $addr->full_name }} - {{ $addr->phone }} | {{ $addr->address_line }} {{ $addr->ward }} {{ $addr->district }} {{ $addr->city }}
                        </option>
                    @endif
                @endforeach
            </select>
            <input type="text" class="address-input" name="address"
                   placeholder="Hoặc nhập địa chỉ khác..."
                   value="{{ old('address', $defaultText) }}">
        </div>

        {{-- Sản phẩm --}}
        <div class="product-summary">
            <h3>Sản Phẩm</h3>
            <div class="product-header">
                <div>Tên sản phẩm</div>
                <div>Số lượng</div>
                <div>Thành tiền</div>
            </div>
            <div class="product-item">
                <div>{{ $product->name }}</div>
                <div>{{ $cart['quantity'] ?? 1 }}</div>
                <div>đ{{ number_format($totalPrice, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Voucher --}}
        <div class="voucher-section">
            <h3>🎟️ Chọn Voucher</h3>
            @php
                $shopVouchers = $shopVouchers ?? collect();
                $adminVouchers = $adminVouchers ?? collect();
            @endphp


            {{-- Voucher của shop --}}
            <label>Voucher cửa hàng</label>
            @if($shopVouchers->count())
                <select name="voucher_shop" class="voucher-select" id="voucher-shop">
                    <option value="">-- Không áp dụng --</option>
                    @foreach($shopVouchers as $v)
                        <option value="{{ $v->id }}" data-discount="{{ $v->discount_amount }}">
                            🏬 {{ $v->code }} - Giảm đ{{ number_format($v->discount_amount,0,',','.') }} (HSD: {{ $v->expiry_date->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            @else
                <p>Không có voucher của shop.</p>
            @endif

            {{-- Voucher toàn hệ thống --}}
            <label>Voucher E-Market (Admin)</label>
            @if($adminVouchers->count())
                <select name="voucher_admin" class="voucher-select" id="voucher-admin">
                    <option value="">-- Không áp dụng --</option>
                    @foreach($adminVouchers as $v)
                        <option value="{{ $v->id }}" data-discount="{{ $v->discount_amount }}">
                            🌐 {{ $v->code }} - Giảm đ{{ number_format($v->discount_amount,0,',','.') }} (HSD: {{ $v->expiry_date->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            @else
                <p>Không có voucher hệ thống.</p>
            @endif
        </div>

        {{-- Ngày giao hàng --}}
        <div class="shipping-info">
            📦 Vận chuyển: Nhận từ {{ $startShippingDate }} - {{ $endShippingDate }}
        </div>

        {{-- Tổng cộng --}}
        <div class="total-section">
            <div class="total-row"><span>Tổng tiền hàng:</span><span id="price">{{ number_format($totalPrice,0,',','.') }}₫</span></div>
            <div class="total-row"><span>Phí vận chuyển:</span><span id="ship">{{ number_format($shippingFee,0,',','.') }}₫</span></div>
            <div class="total-row"><span>Giảm giá voucher:</span><span id="discount">0₫</span></div>
            <div class="total-row total"><span>Tổng thanh toán:</span><strong id="total">{{ number_format($finalTotal,0,',','.') }}₫</strong></div>
        </div>

        <button type="submit" class="place-order-btn">✅ Đặt hàng</button>
    </form>
</div>

<script>
document.querySelectorAll('.voucher-select').forEach(sel => {
    sel.addEventListener('change', updateTotal);
});

function updateTotal() {
    const base = {{ $totalPrice + $shippingFee }};
    const shopDiscount = parseInt(document.querySelector('#voucher-shop')?.selectedOptions[0]?.dataset.discount || 0);
    const adminDiscount = parseInt(document.querySelector('#voucher-admin')?.selectedOptions[0]?.dataset.discount || 0);
    const totalDiscount = (shopDiscount || 0) + (adminDiscount || 0);
    const newTotal = Math.max(base - totalDiscount, 0);

    document.querySelector('#discount').textContent = '-' + totalDiscount.toLocaleString('vi-VN') + '₫';
    document.querySelector('#total').textContent = newTotal.toLocaleString('vi-VN') + '₫';
}

// 🧩 Kiểm tra địa chỉ khi nhấn "Đặt hàng"
document.querySelector('form').addEventListener('submit', function (e) {
    const addressSelect = document.querySelector('.address-select');
    const addressInput  = document.querySelector('.address-input');
    let error = document.querySelector('#address-error');

    // Nếu chưa có phần tử thông báo thì tạo mới
    if (!error) {
        error = document.createElement('p');
        error.id = 'address-error';
        error.style.color = 'red';
        error.style.fontSize = '14px';
        error.style.marginTop = '-5px';
        error.style.marginBottom = '10px';
        addressInput.insertAdjacentElement('afterend', error);
    }

    // Kiểm tra nếu cả 2 đều trống
    if (!addressSelect.value && !addressInput.value.trim()) {
        e.preventDefault();
        error.textContent = '⚠️ Vui lòng chọn hoặc nhập địa chỉ giao hàng.';
        error.style.display = 'block';
        addressSelect.focus();
    } else {
        error.style.display = 'none';
    }
});
</script>

</body>
</html>
