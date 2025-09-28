<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Hiển thị giỏ hàng
    public function index() {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        return view('cart.index', ['cart' => $cart->load('items.product')]);
    }

    // Thêm sản phẩm vào giỏ
    public function add(Request $request, $productId) {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->quantity += $request->input('quantity', 1);
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $request->input('quantity', 1),
            ]);
        }

        return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    // Xóa sản phẩm khỏi giỏ
    public function remove($itemId) {
        CartItem::where('id', $itemId)->delete();
        return redirect()->back()->with('success', 'Đã xóa khỏi giỏ hàng!');
    }
    // Hiển thị trang giỏ hàng riêng
    public function myCart()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        return view('mycart_show', [
            'cart' => $cart->load('items.product')
        ]);
    }

}
