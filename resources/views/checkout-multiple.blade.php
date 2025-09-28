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
    <title>Thanh Toán Nhiều Sản Phẩm - E-Market</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ee4d2d; --text: #333; --muted: #999; --bg: #f5f5f5; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); color: var(--text); }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 4px; }
        .address-section { margin-bottom: 20px; }
        .address-section h3 { color: var(--primary); }
        .address-input, .address-select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }

        /* sản phẩm */
        .product-summary { margin-bottom: 20px; }
        .shop-section { margin-bottom: 30px; padding: 15px; border: 1px solid #eee; border-radius: 8px; }
        .shop-title { font-size: 16px; font-weight: 600; color: var(--primary); margin-bottom: 10px; }
        .product-header, .product-item {
            display: grid;
            grid-template-columns: 1fr 100px 150px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .product-header {
            font-weight: 600;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }
        .product-item div { color: var(--text); }

        .voucher-section { margin-bottom: 20px; }
        .voucher-section button { background: #fff; border: 1px solid #ddd; padding: 5px 10px; border-radius: 4px; color: var(--primary); cursor: pointer; }
        .shipping-info { color: green; margin-bottom: 20px; }
        .payment-section { margin-bottom: 20px; }
        .payment-option { display: flex; align-items: center; margin-bottom: 10px; }
        .payment-option input { margin-right: 10px; }
        .total-section { border-top: 1px solid #eee; padding-top: 20px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .total-row.total { font-weight: 600; font-size: 18px; }
        .place-order-btn { background: var(--primary); color: #fff; padding: 10px 20px; border: none; border-radius: 4px; width: 100%; font-weight: 600; cursor: pointer; }
        
        /* Nút quay lại */
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .back-btn:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Nút quay lại -->
        <a href="{{ route('cart.my') }}" class="back-btn">← Quay lại giỏ hàng</a>

        @if (session('success'))
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 600;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf

            {{-- ✅ Danh sách sản phẩm nhiều theo shop --}}
            <div class="product-summary">
                <h3>Sản Phẩm Được Chọn</h3>
                
                @foreach($grouped as $shopName => $items)
                <div class="shop-section">
                    <div class="shop-title">🛍️ {{ $shopName }}</div>
                    
                    <div class="product-header">
                        <div>Tên sản phẩm</div>
                        <div>Số lượng</div>
                        <div>Thành tiền</div>
                    </div>
                    
                    @foreach($items as $item)
                        @php
                            $subtotal = $item->product->price * $item->quantity;
                        @endphp
                        <input type="hidden" name="items[{{ $item->product->id }}][product_id]" value="{{ $item->product->id }}">
                        <input type="hidden" name="items[{{ $item->product->id }}][quantity]" value="{{ $item->quantity }}">
                        <input type="hidden" name="items[{{ $item->product->id }}][seller_id]" value="{{ $item->product->seller_id }}">
                        
                        <div class="product-item">
                            <div>{{ $item->product->name }}</div>
                            <div>{{ $item->quantity }}</div>
                            <div>đ{{ number_format($subtotal, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
                @endforeach
            </div>

            {{-- Địa chỉ --}}
            <div class="address-section">
                <h3>Địa Chỉ Nhận Hàng</h3>
                @php
                    $defaultText = $defaultAddress ? ($defaultAddress->full_name.' - '.$defaultAddress->phone.' | '.$defaultAddress->address_line.' '.$defaultAddress->ward.' '.$defaultAddress->district.' '.$defaultAddress->city) : '';
                @endphp
                <select name="address_id" class="address-select">
                    <option value="">-- Chọn địa chỉ giao hàng --</option>
                    @if($defaultAddress)
                        <option value="{{ $defaultAddress->id }}" selected> Mặc định: {{ $defaultText }}</option>
                    @endif
                    @foreach(($addresses ?? collect()) as $addr)
                        @if(!$defaultAddress || $addr->id !== $defaultAddress->id)
                            <option value="{{ $addr->id }}">{{ $addr->full_name }} - {{ $addr->phone }} | {{ $addr->address_line }} {{ $addr->ward }} {{ $addr->district }} {{ $addr->city }}</option>
                        @endif
                    @endforeach
                </select>
                <input type="text" class="address-input" name="address" placeholder="Hoặc nhập địa chỉ khác..." value="{{ old('address', $defaultText) }}">
                @error('address')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Voucher --}}
            <div class="voucher-section">
                <h3>Voucher</h3>
                <button type="button">Chọn Voucher Khác</button>
                <p>Giảm đ{{ number_format($discount ?? 10000, 0, ',', '.') }} (Hết hạn: 15 Th09 2025)</p>
            </div>

            {{-- Shipping --}}
            <div class="shipping-info">
                📦 Vận chuyển: Nhận từ {{ $startShippingDate }} - {{ $endShippingDate }}
            </div>

            {{-- Payment --}}
            <div class="payment-section">
                <h3>Phương thức thanh toán</h3>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="shopeepay" id="shopeepay" checked>
                    <label for="shopeepay">ShopeePay</label>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="vcb" id="vcb">
                    <label for="vcb">VCB (Số tài khoản: 52937)</label>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="google_pay" id="google_pay">
                    <label for="google_pay">Google Pay</label>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="napas" id="napas">
                    <label for="napas">Thẻ nội địa NAPAS</label>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="credit_card" id="credit_card">
                    <label for="credit_card">Trả góp bằng Thẻ Tín dụng</label>
                </div>
                @error('payment_method')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tổng cộng --}}
            <div class="total-section">
                <div class="total-row">Tổng tiền hàng: đ{{ number_format($totalPrice ?? 0, 0, ',', '.') }}</div>
                <div class="total-row">Phí vận chuyển: đ{{ number_format($shippingFee ?? 38000, 0, ',', '.') }}</div>
                <div class="total-row">Giảm giá voucher: -đ{{ number_format($discount ?? 10000, 0, ',', '.') }}</div>
                <div class="total-row total">Tổng thanh toán: đ{{ number_format($finalTotal ?? 0, 0, ',', '.') }}</div>
            </div>

            <button type="submit" class="place-order-btn">✅ Đặt hàng</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validate form trước khi submit
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const addressSelect = document.querySelector('select[name="address_id"]');
                const addressInput = document.querySelector('input[name="address"]');
                
                // Kiểm tra địa chỉ
                if (!addressSelect.value && !addressInput.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng chọn hoặc nhập địa chỉ giao hàng!');
                    return false;
                }
                
                // Kiểm tra phương thức thanh toán
                const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
                if (!paymentSelected) {
                    e.preventDefault();
                    alert('Vui lòng chọn phương thức thanh toán!');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>