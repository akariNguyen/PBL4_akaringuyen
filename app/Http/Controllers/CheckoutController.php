<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    /**
     * 🧹 Xóa voucher hết hạn khi controller được gọi
     */
    public function __construct()
    {
        Voucher::where('expiry_date', '<', now())->delete();
    }

    /**
     * 🛒 Hiển thị checkout cho 1 sản phẩm (Mua ngay)
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);

        $cart = [
            'product_id' => $productId,
            'quantity'   => request()->input('quantity', 1),
        ];
        Session::put('cart', $cart);

        $totalPrice   = $product->price * $cart['quantity'];
        $shippingFee  = 38000;
        $discount     = 0;
        $finalTotal   = $totalPrice + $shippingFee;

        $addresses      = Auth::check() ? Auth::user()->addresses()->latest()->get() : collect();
        $defaultAddress = Auth::check() ? Auth::user()->defaultAddress()->first() : null;

        // ✅ Voucher riêng của shop
        $shopVouchers = Voucher::where('shop_id', $product->seller_id)
            ->where('status', 'active')
            ->where('expiry_date', '>=', now())
            ->get();

        // ✅ Voucher toàn hệ thống
        $adminVouchers = Voucher::whereNull('shop_id')
            ->where('status', 'active')
            ->where('expiry_date', '>=', now())
            ->get();

        return view('checkout', compact(
            'product',
            'cart',
            'totalPrice',
            'shippingFee',
            'discount',
            'finalTotal',
            'addresses',
            'defaultAddress',
            'shopVouchers',
            'adminVouchers'
        ));
    }

    /**
     * 💾 Lưu đơn hàng (Mua ngay hoặc từ giỏ hàng)
     */
    public function store(Request $request)
    {
        $request->validate([
            'address'    => 'nullable|string|max:255',
            'address_id' => 'nullable|integer',
        ]);

        // ✅ Lấy địa chỉ giao hàng
        $shippingAddress = $request->input('address');
        if ($request->filled('address_id')) {
            $addr = Address::where('user_id', Auth::id())->find($request->integer('address_id'));
            if ($addr) {
                $shippingAddress = trim(
                    $addr->address_line . ' ' . ($addr->ward ?? '') . ' ' . ($addr->district ?? '') . ' ' . ($addr->city ?? '')
                );
            }
        }

        if (!$shippingAddress) {
            return back()->withErrors(['address' => 'Vui lòng chọn hoặc nhập địa chỉ giao hàng!']);
        }

        /**
         * === CASE 1: Mua ngay ===
         */
        if (Session::has('cart')) {
            $cart = Session::get('cart');
            $product = Product::findOrFail($cart['product_id']);

            if ($cart['quantity'] > $product->quantity) {
                return back()->withErrors(['quantity' => 'Số lượng vượt quá tồn kho!']);
            }

            $totalPrice  = $product->price * $cart['quantity'];
            $shippingFee = 38000;
            $discount    = 0;

            // ✅ Áp dụng voucher
            if ($request->filled('voucher_shop')) {
                $v = Voucher::find($request->input('voucher_shop'));
                if ($v && $v->expiry_date >= now() && $v->status === 'active') {
                    $discount += $v->discount_amount;
                }
            }
            if ($request->filled('voucher_admin')) {
                $v = Voucher::find($request->input('voucher_admin'));
                if ($v && $v->expiry_date >= now() && $v->status === 'active') {
                    $discount += $v->discount_amount;
                }
            }

            $finalTotal = max($totalPrice + $shippingFee - $discount, 0);

            $order = Order::create([
                'user_id'     => Auth::id(),
                'total_price' => $finalTotal,
                'address'     => $shippingAddress,
                'status'      => 'pending',
            ]);

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product->id,
                'seller_id'    => $product->seller_id,
                'product_name' => $product->name,
                'price'        => $product->price,
                'quantity'     => $cart['quantity'],
            ]);

            // ✅ Cập nhật tồn kho & sold_quantity
            $product->increment('sold_quantity', $cart['quantity']);
            $product->decrement('quantity', $cart['quantity']);

            Session::forget('cart');
        }

        /**
         * === CASE 2: Thanh toán nhiều sản phẩm từ giỏ hàng ===
         */
        elseif ($request->has('items')) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product.seller.shop')->first();

            // 🔹 Lấy danh sách sản phẩm dạng [id => quantity]
            // LẤY items từ request và chuẩn hóa về dạng Collection: [product_id => quantity]
$itemsData = collect($request->input('items', []))
    ->mapWithKeys(function ($val, $key) {
        // Hỗ trợ 2 kiểu form:
        // a) items[][id]=123, items[][quantity]=2  → $val=['id'=>123,'quantity'=>2]
        // b) items[123][quantity]=2                → $key=123, $val=['quantity'=>2]
        if (is_array($val) && isset($val['id'])) {
            return [(int) $val['id'] => (int) ($val['quantity'] ?? 1)];
        }
        return [(int) $key => (int) ($val['quantity'] ?? 1)];
    });

// Lọc item trong giỏ theo product_id đã chọn
$selectedItems = $cart->items->whereIn('product_id', $itemsData->keys());

// Gán lại số lượng thực tế từ request
foreach ($selectedItems as $item) {
    $item->quantity = (int) ($itemsData[$item->product_id] ?? $item->quantity);
}


            // ✅ Nhóm theo shop
            $groupedItems = $selectedItems->groupBy(fn($i) => optional($i->product->seller->shop)->user_id ?? 0);
            $selectedVouchers = $request->input('vouchers', []);
            $shippingFee = 38000;
            $totalAllOrders = 0;

            foreach ($groupedItems as $shopId => $items) {
                $shopTotal = $items->sum(fn($i) => $i->product->price * $i->quantity);
                $discount = 0;

                // 🎟️ Voucher shop
                if (!empty($selectedVouchers[$shopId])) {
                    $voucher = Voucher::find($selectedVouchers[$shopId]);
                    if ($voucher && $voucher->status === 'active' && $voucher->expiry_date >= now()) {
                        $discount = $voucher->discount_amount;
                    }
                }

                $finalShopTotal = max($shopTotal - $discount + $shippingFee, 0);
                $totalAllOrders += $finalShopTotal;

                // 🧾 Tạo đơn riêng cho shop
                $order = Order::create([
                    'user_id'     => Auth::id(),
                    'total_price' => $finalShopTotal,
                    'address'     => $shippingAddress,
                    'status'      => 'pending',
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item->product->id,
                        'seller_id'    => $item->product->seller_id,
                        'product_name' => $item->product->name,
                        'price'        => $item->product->price,
                        'quantity'     => $item->quantity,
                    ]);

                    // ✅ Cập nhật kho
                    $item->product->increment('sold_quantity', $item->quantity);
                    $item->product->decrement('quantity', $item->quantity);
                }

                // 🗑️ Xóa sản phẩm đã thanh toán khỏi giỏ
                $cart->items()
                    ->whereIn('product_id', $items->pluck('product_id'))
                    ->delete();
            }

            // 🌐 Voucher toàn hệ thống (admin)
            if ($request->filled('admin_voucher')) {
                $adminVoucher = Voucher::find($request->input('admin_voucher'));
                if ($adminVoucher && $adminVoucher->status === 'active' && $adminVoucher->expiry_date >= now()) {
                    $totalAllOrders = max($totalAllOrders - $adminVoucher->discount_amount, 0);
                }
            }
        }

        return redirect()->route('orders.my')->with('success', 'Đặt hàng thành công!');
    }

    /**
     * ✅ Hiển thị checkout nhiều sản phẩm (nhiều shop)
     */
    public function fromCart(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cart = $user->cart()->with('items.product.seller.shop')->first();
        if (!$cart) return redirect()->route('cart.my')->withErrors(['cart' => 'Giỏ hàng trống.']);

        $items = (array) $request->input('items', []);
        if (empty($items)) {
            return redirect()->route('cart.my')->withErrors(['cart' => 'Bạn chưa chọn sản phẩm nào để thanh toán.']);
        }

        // 🟢 Lấy đúng cấu trúc [id => quantity]
        // Chuẩn hóa items về dạng [product_id => quantity]
$itemsData = collect($request->input('items', []))
    ->mapWithKeys(function ($val, $key) {
        if (is_array($val) && isset($val['id'])) {
            return [(int) $val['id'] => (int) ($val['quantity'] ?? 1)];
        }
        return [(int) $key => (int) ($val['quantity'] ?? 1)];
    });

// Lấy item trong giỏ theo danh sách product_id
$selectedItems = $cart->items->whereIn('product_id', $itemsData->keys());

// Gán lại số lượng đúng để render
foreach ($selectedItems as $item) {
    $item->quantity = (int) ($itemsData[$item->product_id] ?? $item->quantity);
}

        // ✅ Nhóm theo shop
        $grouped = $selectedItems->groupBy(fn($i) => optional($i->product->seller->shop)->user_id ?? 0)->sortKeys();
        $shops = $selectedItems->map(fn($i) => $i->product->seller->shop)->filter()->keyBy('user_id');

        // ✅ Voucher shop
        $shopIds = $grouped->keys();
        $shopVouchers = Voucher::whereIn('shop_id', $shopIds)
            ->where('expiry_date', '>=', now())
            ->where('status', 'active')
            ->get()
            ->groupBy('shop_id');

        // ✅ Voucher admin
        $adminVouchers = Voucher::whereNull('shop_id')
            ->where('expiry_date', '>=', now())
            ->where('status', 'active')
            ->get();

        $addresses      = $user->addresses()->latest()->get();
        $defaultAddress = $user->defaultAddress()->first();

        $shippingFee = 38000;
        $finalTotal  = $selectedItems->sum(fn($i) => $i->product->price * $i->quantity) + $shippingFee;

        return view('checkout-multiple', compact(
            'grouped', 'shops', 'shopVouchers', 'adminVouchers',
            'addresses', 'defaultAddress', 'shippingFee', 'finalTotal'
        ));
    }
}
