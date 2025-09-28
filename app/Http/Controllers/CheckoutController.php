<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page for a specific product.
     *
     * @param int $productId
     * @return \Illuminate\View\View
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        $cart = [
            'product_id' => $productId,
            'quantity' => request()->input('quantity', 1)
        ];
        Session::put('cart', $cart);

        $totalPrice = $product->price * $cart['quantity'];
        $shippingFee = 38000;
        $discount = 10000;
        $finalTotal = $totalPrice + $shippingFee - $discount;
        $addresses = auth()->check() ? auth()->user()->addresses()->latest()->get() : collect();
        $defaultAddress = auth()->check() ? auth()->user()->defaultAddress()->first() : null;

        return view('checkout', compact('product', 'cart', 'totalPrice', 'shippingFee', 'discount', 'finalTotal', 'addresses', 'defaultAddress'));
    }

    /**
     * Store a new order in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'payment_method' => 'required|in:shopeepay,vcb,google_pay,napas,credit_card',
            'quantity' => 'required|integer|min:1',
            'address' => 'nullable|string|max:255',
            'address_id' => 'nullable|integer'
        ]);

        // Resolve shipping address: prefer address_id, fallback to address text
        $shippingAddress = $request->input('address');
        if ($request->filled('address_id')) {
            $addr = \App\Models\Address::where('user_id', auth()->id())->find($request->input('address_id'));
            if ($addr) {
                $shippingAddress = trim($addr->address_line.' '.($addr->ward ?? '').' '.($addr->district ?? '').' '.($addr->city ?? ''));
            }
        }
        if (!$shippingAddress) {
            return redirect()->back()->withErrors(['address' => 'Vui lòng chọn hoặc nhập địa chỉ giao hàng!']);
        }

        // Get the product from the cart
        $cart = Session::get('cart', ['product_id' => $request->input('product_id'), 'quantity' => $request->input('quantity')]);
        $product = Product::findOrFail($cart['product_id']);

        // Check if there is enough stock
        $availableStock = $product->quantity - ($product->sold_quantity ?? 0);
        if ($cart['quantity'] > $availableStock) {
            return redirect()->back()->withErrors(['quantity' => 'Số lượng vượt quá tồn kho!']);
        }

        // Calculate total price
        $totalPrice = $product->price * $cart['quantity'];
        $shippingFee = 38000; // Example shipping fee
        $discount = 10000; // Example voucher discount
        $finalTotal = $totalPrice + $shippingFee - $discount;

        // Create a new order
        $order = new Order();
        $order->user_id = auth()->id(); // Assuming authenticated user
        $order->total_price = $finalTotal;
        $order->address = $shippingAddress;
        $order->status = 'pending';
        $order->save();

        // Create OrderItem
        $orderItem = new OrderItem();
        $orderItem->order_id = $order->id;
        $orderItem->product_id = $product->id;
        $orderItem->seller_id = $product->seller_id; // Giả định seller_id là user_id của seller
        $orderItem->product_name = $product->name;
        $orderItem->price = $product->price;
        $orderItem->quantity = $cart['quantity'];
        $orderItem->save();

        // Update product sold_quantity
        $product->sold_quantity = ($product->sold_quantity ?? 0) + $cart['quantity'];
        $product->save();

        // Clear cart or update session as needed
        Session::forget('cart');

        // Redirect to a success page or back with success message
        return redirect()->route('customer.dashboard')->with('success', 'Đặt hàng thành công!');
    }

    /**
     * Display the order success page.
     *
     * @return \Illuminate\View\View
     */
    public function success()
    {
        return view('checkout-success')->with('message', 'Cảm ơn bạn đã đặt hàng!');
    }

    /**
     * Checkout from cart with multiple items
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    /**
 * Checkout from cart with multiple items
 *
 * @param Request $request
 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
 */
public function fromCart(Request $request)
{
    // ✅ Debug log để kiểm tra dữ liệu nhận được
    \Log::info('Checkout from cart request:', [
        'user_id' => auth()->id(),
        'selected_items' => $request->items,
        'all_input' => $request->all()
    ]);

    $cart = auth()->user()->cart()->with('items.product.seller.shop')->first();

    // ✅ Kiểm tra kỹ hơn nếu không có items nào được chọn
    if (empty($request->items)) {
        \Log::warning('No items selected for checkout', ['user_id' => auth()->id()]);
        return redirect()->route('cart.my')->withErrors(['cart' => 'Bạn chưa chọn sản phẩm nào để thanh toán.']);
    }

    // Lọc ra những sản phẩm người dùng tick chọn trong giỏ
    $selectedItems = $cart->items->whereIn('product_id', $request->items ?? []);

    if ($selectedItems->isEmpty()) {
        \Log::warning('Selected items not found in cart', [
            'user_id' => auth()->id(),
            'requested_items' => $request->items
        ]);
        return redirect()->route('cart.my')->withErrors(['cart' => 'Bạn chưa chọn sản phẩm nào để thanh toán.']);
    }

    // ✅ Kiểm tra tồn kho cho tất cả sản phẩm được chọn
    foreach ($selectedItems as $item) {
        $availableStock = $item->product->quantity - ($item->product->sold_quantity ?? 0);
        if ($item->quantity > $availableStock) {
            return redirect()->route('cart.my')->withErrors([
                'cart' => "Sản phẩm {$item->product->name} vượt quá tồn kho! Chỉ còn {$availableStock} sản phẩm."
            ]);
        }
    }

    // Nhóm theo shop, sắp xếp A->Z
    $grouped = $selectedItems->groupBy(fn($item) => $item->product->seller->shop->name ?? 'Không có Shop')
                            ->sortKeys();

    $addresses = auth()->user()->addresses()->latest()->get();
    $defaultAddress = auth()->user()->defaultAddress()->first();

    // ✅ Tính tổng tiền
    $totalPrice = $selectedItems->sum(function($item) {
        return $item->product->price * $item->quantity;
    });
    $shippingFee = 38000;
    $discount = 10000;
    $finalTotal = $totalPrice + $shippingFee - $discount;

    // ✅ Return view checkout-multiple (đúng tên file)
    return view('checkout-multiple', compact(
        'grouped', 
        'addresses', 
        'defaultAddress', 
        'totalPrice', 
        'shippingFee', 
        'discount', 
        'finalTotal'
    ));
}
}