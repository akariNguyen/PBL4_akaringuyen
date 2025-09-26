<?php
// Calculate shipping dates
$currentDate = new DateTime();
$startShippingDate = (clone $currentDate)->modify('+3 days')->format('d M');
$endShippingDate = (clone $currentDate)->modify('+5 days')->format('d M');
$totalPrice = $product->price * ($cart['quantity'] ?? 1);
$shippingFee = 38000; // Example shipping fee in VND
$discount = 10000; // Example voucher discount
$finalTotal = $totalPrice + $shippingFee - $discount;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - E-Market</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #ee4d2d; --text: #333; --muted: #999; --bg: #f5f5f5; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); color: var(--text); }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 4px; }
        .address-section { margin-bottom: 20px; }
        .address-section h3 { color: var(--primary); }
        .address-input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .address-select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }

        /* sản phẩm */
        .product-summary { margin-bottom: 20px; }
        .product-header, .product-item {
            display: grid;
            grid-template-columns: 1fr 100px 150px; /* 3 cột */
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
    </style>
</head>
<body>
    <div class="container">
        @if (session('success'))
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 600;">
                {{ session('success') }}
            </div>
        @endif
        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="quantity" value="{{ $cart['quantity'] ?? 1 }}">

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
                <p style="color: red;">{{ $errors->first('address') }}</p>
            </div>

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

            <div class="voucher-section">
                <h3>Voucher cửa Shop</h3>
                <button type="button">Chọn Voucher Khác</button>
                <p>Giảm đ{{ number_format($discount, 0, ',', '.') }} (Hết hạn: 15 Th09 2025)</p>
            </div>

            <div class="shipping-info">
                Vận chuyển: Nhận từ {{ $startShippingDate }} - {{ $endShippingDate }}
            </div>

            <div class="payment-section">
                <h3>Phương thức thanh toán</h3>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="shopeepay" checked>
                    <span>ShopeePay</span>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="vcb">
                    <span>VCB (Số tài khoản: 52937)</span>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="google_pay">
                    <span>Google Pay</span>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="napas">
                    <span>Thẻ nội địa NAPAS</span>
                </div>
                <div class="payment-option">
                    <input type="radio" name="payment_method" value="credit_card">
                    <span>Trả góp bằng Thẻ Tín dụng</span>
                </div>
            </div>

            <div class="total-section">
                <div class="total-row">Tổng tiền hàng: đ{{ number_format($totalPrice, 0, ',', '.') }}</div>
                <div class="total-row">Tổng tiền phí vận chuyển: đ{{ number_format($shippingFee, 0, ',', '.') }}</div>
                <div class="total-row">Tổng cộng Voucher giảm giá: -đ{{ number_format($discount, 0, ',', '.') }}</div>
                <div class="total-row total">Tổng thanh toán: đ{{ number_format($finalTotal, 0, ',', '.') }}</div>
            </div>

            <button type="submit" class="place-order-btn">Đặt hàng</button>
        </form>
    </div>
</body>
</html>