<?php
// Tính ngày giao hàng (3–5 ngày sau)
$currentDate = new DateTime();
$startShippingDate = (clone $currentDate)->modify('+3 days')->format('d M');
$endShippingDate = (clone $currentDate)->modify('+5 days')->format('d M');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Nhiều Sản Phẩm - E-Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ee4d2d; --text: #333; --muted: #999; --bg: #f5f5f5; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); color: var(--text); }
        .container { max-width: 850px; margin: 20px auto; padding: 25px; background: #fff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

        .address-section, .product-summary, .payment-section { margin-bottom: 25px; }
        h3 { color: var(--primary); margin-bottom: 10px; }

        .address-input, .address-select, .voucher-select {
            width: 100%; padding: 10px; border: 1px solid #ddd;
            border-radius: 4px; margin-bottom: 10px;
        }

        .shop-section {
            margin-bottom: 25px; padding: 15px 20px;
            border: 1px solid #eee; border-radius: 8px;
            background: #fafafa;
        }

        .shop-title {
            font-size: 16px; font-weight: 600;
            color: var(--primary); margin-bottom: 10px;
        }

        .product-header, .product-item {
            display: grid; grid-template-columns: 1fr 100px 150px;
            padding: 10px 0; border-bottom: 1px solid #eee; align-items: center;
        }

        .product-header { font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--primary); }

        .voucher-section h4 { color: var(--primary); margin-bottom: 5px; }

        .total-row { display: flex; justify-content: space-between; margin: 10px 0; }
        .total-row.total { font-weight: 700; font-size: 18px; border-top: 1px solid #eee; padding-top: 10px; }

        .place-order-btn {
            background: var(--primary); color: #fff;
            padding: 12px 20px; border: none; border-radius: 4px;
            width: 100%; font-weight: 600; cursor: pointer;
        }

        .back-btn {
            display: inline-block; background: #6c757d;
            color: #fff; padding: 8px 16px; border-radius: 4px;
            text-decoration: none; margin-bottom: 20px;
        }
        .back-btn:hover { background: #5a6268; }

        .shipping-info { color: green; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container">
    <a href="{{ route('cart.my') }}" class="back-btn">← Quay lại giỏ hàng</a>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf

        {{-- Địa chỉ nhận hàng --}}
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

        {{-- Nhóm sản phẩm theo từng shop --}}
        <div class="product-summary">
            <h3>Sản Phẩm Được Chọn</h3>
            @foreach($grouped as $shopId => $items)
                @php
                    $shop = $shops[$shopId] ?? null;
                    $shopSubtotal = $items->sum(fn($i) => $i->product->price * $i->quantity);
                    $shopTotal = $shopSubtotal + $shippingFee;
                @endphp

                <div class="shop-section">
                    <div class="shop-title">🛍️ {{ $shop->name ?? 'Không rõ Shop' }}</div>

                    <div class="product-header">
                        <div>Tên sản phẩm</div>
                        <div>Số lượng</div>
                        <div>Thành tiền</div>
                    </div>

                    @foreach($items as $item)
                        @php $subtotal = $item->product->price * $item->quantity; @endphp
                        <div class="product-item">
                            <div>{{ $item->product->name }}</div>
                            <div>{{ $item->quantity }}</div>
                            <div>đ{{ number_format($subtotal, 0, ',', '.') }}</div>
                        </div>
                    @endforeach

                    {{-- 🎟️ Chọn Voucher --}}
                    <div class="voucher-section">
                        <h4>🎟️ Chọn Voucher</h4>
                        @if(isset($vouchers[$shopId]) && count($vouchers[$shopId]) > 0)
                            <select name="vouchers[{{ $shopId }}]" class="voucher-select" data-shop="{{ $shopId }}">
                                <option value="">-- Không áp dụng voucher --</option>
                                @foreach($vouchers[$shopId] as $v)
                                    <option value="{{ $v->id }}" data-discount="{{ $v->discount_amount }}">
                                        {{ $v->code }} - Giảm đ{{ number_format($v->discount_amount, 0, ',', '.') }} (HSD: {{ $v->expiry_date->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <p>Không có voucher nào khả dụng.</p>
                        @endif
                    </div>
                    <p class="mb-1">
                        <strong>Phí vận chuyển:</strong> 
                        <span class="text-muted">{{ number_format($shippingFee, 0, ',', '.') }}₫</span>
                    </p>
                    {{-- Tổng shop sau giảm --}}
                    <div class="total-row shop-total" id="shop-total-{{ $shopId }}">
                        <span>Tổng shop sau giảm:</span>
                        <strong data-base="{{ $shopTotal }}">đ{{ number_format($shopTotal, 0, ',', '.') }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Giao hàng --}}
        <div class="shipping-info">
            📦 Vận chuyển: Nhận từ {{ $startShippingDate }} - {{ $endShippingDate }}
        </div>

        {{-- Thanh toán --}}
        <div class="payment-section">
            <h3>Phương thức thanh toán</h3>
            @foreach([
                'shopeepay' => 'ShopeePay',
                'vcb' => 'VCB (Số tài khoản: 52937)',
                'google_pay' => 'Google Pay',
                'napas' => 'Thẻ nội địa NAPAS',
                'credit_card' => 'Trả góp bằng Thẻ Tín dụng'
            ] as $value => $label)
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="{{ $value }}" id="{{ $value }}" {{ $loop->first ? 'checked' : '' }}>
                    <label for="{{ $value }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>

        {{-- Tổng cộng toàn đơn --}}
        <div class="total-section">
            <div class="total-row total" id="final-total">
                <span>Tổng thanh toán:</span>
                <strong>đ{{ number_format($finalTotal ?? 0, 0, ',', '.') }}</strong>
            </div>
        </div>

        <button type="submit" class="place-order-btn">✅ Đặt hàng</button>
    </form>
</div>

{{-- 🧠 Script tính toán động --}}
<script>
document.querySelectorAll('.voucher-select').forEach(select => {
    select.addEventListener('change', function() {
        const shopId = this.dataset.shop;
        const discount = parseInt(this.selectedOptions[0]?.dataset.discount || 0);
        const totalRow = document.querySelector(`#shop-total-${shopId} strong`);
        const base = parseInt(totalRow.dataset.base || 0);

        const newTotal = Math.max(base - discount, 0);
        totalRow.textContent = 'đ' + newTotal.toLocaleString('vi-VN');

        updateFinalTotal();
    });
});

function updateFinalTotal() {
    let total = 0;
    document.querySelectorAll('.shop-total strong').forEach(el => {
        const val = parseInt(el.textContent.replace(/[^\d]/g, '') || 0);
        total += val;
    });
    document.querySelector('#final-total strong').textContent = 'đ' + total.toLocaleString('vi-VN');
}
</script>
</body>
</html>
