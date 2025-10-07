<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
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
    $discount     = 0; // chưa chọn voucher thì chưa giảm
    $finalTotal   = $totalPrice + $shippingFee - $discount;

    $addresses      = Auth::check() ? Auth::user()->addresses()->latest()->get() : collect();
    $defaultAddress = Auth::check() ? Auth::user()->defaultAddress()->first() : null;

    return view('checkout', compact(
        'product',
        'cart',
        'totalPrice',
        'shippingFee',
        'discount',
        'finalTotal',
        'addresses',
        'defaultAddress'
    ));
}


    /**
     * 💾 Lưu đơn hàng (Mua ngay hoặc từ giỏ hàng)
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:shopeepay,vcb,google_pay,napas,credit_card',
            'address'        => 'nullable|string|max:255',
            'address_id'     => 'nullable|integer',
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

            $availableStock = $product->quantity - ($product->sold_quantity ?? 0);
            if ($cart['quantity'] > $availableStock) {
                return back()->withErrors(['quantity' => 'Số lượng vượt quá tồn kho!']);
            }

            $totalPrice  = $product->price * $cart['quantity'];
            $shippingFee = 38000;
            $discount    = 10000;
            $finalTotal  = $totalPrice + $shippingFee - $discount;

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

            $product->increment('sold_quantity', $cart['quantity']);
            Session::forget('cart');
        }

        /**
         * === CASE 2: Thanh toán từ giỏ hàng (nhiều shop + nhiều voucher) ===
         */
        elseif ($request->has('items')) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product.seller.shop')->first();

            $selectedItems = $cart->items->whereIn('product_id', $request->input('items'));
            if ($selectedItems->isEmpty()) {
                return redirect()->route('cart.my')->withErrors(['cart' => 'Không tìm thấy sản phẩm để thanh toán.']);
            }

            // ✅ Nhóm sản phẩm theo shop
            $groupedItems = $selectedItems->groupBy(fn($i) => optional($i->product->seller->shop)->user_id ?? 0);

            // ✅ Xử lý voucher theo từng shop
            $selectedVouchers = $request->input('vouchers', []);
            $shippingFee = 38000;
            $finalTotal = 0;

            foreach ($groupedItems as $shopId => $items) {
                $shopTotal = $items->sum(fn($i) => $i->product->price * $i->quantity);
                $discount = 0;

                if (!empty($selectedVouchers[$shopId])) {
                    $voucher = Voucher::find($selectedVouchers[$shopId]);
                    if ($voucher && $voucher->status === 'active' && $voucher->expiry_date >= now()) {
                        $discount = $voucher->discount_amount;
                    }
                }

                $finalTotal += $shopTotal - $discount + $shippingFee;
            }

            // ✅ Tạo đơn hàng
            $order = Order::create([
                'user_id'     => Auth::id(),
                'total_price' => $finalTotal,
                'address'     => $shippingAddress,
                'status'      => 'pending',
            ]);

            // ✅ Thêm từng item vào OrderItem
            foreach ($selectedItems as $item) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product->id,
                    'seller_id'    => $item->product->seller_id,
                    'product_name' => $item->product->name,
                    'price'        => $item->product->price,
                    'quantity'     => $item->quantity,
                ]);

                $item->product->increment('sold_quantity', $item->quantity);
            }
        }

        return redirect()->route('customer.dashboard')->with('success', 'Đặt hàng thành công!');
    }

    /**
     * ✅ Trang cảm ơn
     */
    public function success()
    {
        return view('checkout-success')->with('message', 'Cảm ơn bạn đã đặt hàng!');
    }

    /**
     * ✅ Hiển thị checkout nhiều sản phẩm (có nhiều voucher mỗi shop)
     */
    public function fromCart(Request $request)
    {
        Log::info('Checkout from cart request', [
            'user_id'        => Auth::id(),
            'selected_items' => $request->input('items'),
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $cart = $user->cart()->with('items.product.seller.shop')->first();
        if (!$cart) {
            return redirect()->route('cart.my')->withErrors(['cart' => 'Giỏ hàng trống.']);
        }

        $items = (array) $request->input('items', []);
        if (empty($items)) {
            return redirect()->route('cart.my')->withErrors(['cart' => 'Bạn chưa chọn sản phẩm nào để thanh toán.']);
        }

        $selectedItems = $cart->items->whereIn('product_id', $items);
        if ($selectedItems->isEmpty()) {
            return redirect()->route('cart.my')->withErrors(['cart' => 'Không tìm thấy sản phẩm để thanh toán.']);
        }

        // ✅ Nhóm theo shop
        $grouped = $selectedItems
            ->groupBy(fn($i) => optional($i->product->seller->shop)->user_id ?? 0)
            ->sortKeys();

        // ✅ Lấy danh sách shop
        $shops = $selectedItems
            ->map(fn($i) => $i->product->seller->shop)
            ->filter()
            ->keyBy('user_id');

        // ✅ Lấy tất cả voucher khả dụng của từng shop
        $shopIds = $grouped->keys();
        $vouchers = Voucher::whereIn('shop_id', $shopIds)
            ->where('expiry_date', '>', now())
            ->where('status', 'active')
            ->get()
            ->groupBy('shop_id'); // -> mỗi shop có thể có nhiều voucher

        $addresses      = $user->addresses()->latest()->get();
        $defaultAddress = $user->defaultAddress()->first();

        // Tổng tạm tính (chưa trừ voucher)
        $shippingFee = 38000;
        $finalTotal  = $selectedItems->sum(fn($i) => $i->product->price * $i->quantity) + $shippingFee;

        return view('checkout-multiple', compact(
            'grouped', 'shops', 'vouchers', 'addresses', 'defaultAddress',
            'shippingFee', 'finalTotal'
        ));
    }
}
