<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function create()
{
    $user = Auth::user();

    // ✅ Kiểm tra đăng nhập và vai trò
    if (!$user || $user->role !== 'seller') {
        abort(403, 'Chỉ người bán mới được truy cập trang này.');
    }

    // ✅ Lấy shop của user
    $shop = \App\Models\Shop::where('user_id', $user->id)->first();

    // ❌ Nếu không có shop hoặc shop bị đình chỉ → chặn
    if (!$shop || $shop->status === 'suspended') {
        return redirect()
            ->route('seller.dashboard')
            ->with('error', '🚫 Shop của bạn đang bị đình chỉ — không thể thêm sản phẩm mới.');
    }

    // ✅ Cho phép truy cập nếu hợp lệ
    return view('product_create');
}


   public function show(Request $request, $id)
{
    // Lấy sản phẩm kèm shop và người bán
    $product = Product::with(['reviews.user', 'seller.shop'])->findOrFail($id);

    // ❌ Nếu sản phẩm hết hàng hoặc shop không hoạt động → ẩn / lỗi 404
    if ($product->status !== 'in_stock' || !$product->seller || !$product->seller->shop || $product->seller->shop->status !== 'active') {
        abort(404, 'Sản phẩm không khả dụng hoặc shop đã bị tạm ngưng.');
    }

    // --- Phần còn lại giữ nguyên ---
    $avgRating = $product->reviews()->avg('rating');

    $filter = $request->get('filter', 'all');
    $reviewsQuery = $product->reviews()->with('user');

    switch ($filter) {
        case '5stars': $reviewsQuery->where('rating', 5); break;
        case '4stars': $reviewsQuery->where('rating', 4); break;
        case '3stars': $reviewsQuery->where('rating', 3); break;
        case '2stars': $reviewsQuery->where('rating', 2); break;
        case '1star':  $reviewsQuery->where('rating', 1); break;
        case 'oldest': $reviewsQuery->oldest(); break;
        case 'newest': $reviewsQuery->latest(); break;
        default:       $reviewsQuery->latest(); break;
    }

    $reviews = $reviewsQuery->get();
    $variations = [];

    $shop = $product->seller && $product->seller->role === 'seller'
        ? $product->seller->shop
        : null;

    return view('product_show', compact('product', 'variations', 'avgRating', 'reviews', 'filter', 'shop'));
}





    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',

            'images.*'    => 'nullable|image|max:4096',
        ]);

        // Tìm hoặc tạo category
        $category = Category::firstOrCreate(
            ['name' => mb_strtolower($validated['category'])],
            ['description' => null]
        );

        $product = Product::create([
            'seller_id'   => $user->id,
            'category_id' => $category->id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'],
            'quantity'    => $validated['quantity'],
               'status'      => 'pending',
        ]);

        if ($request->hasFile('images')) {
            $stored = [];
            foreach ($request->file('images') as $file) {
                $stored[] = $file->store('products', 'public');
            }
            $product->images = $stored;
            $product->save();
        }

          return redirect()->route('seller.dashboard')->with('success', 'Sản phẩm đã được tạo và đang chờ duyệt.');
    }

    public function update(Request $request, $id)
{
    $user = Auth::user();
    if (!$user || $user->role !== 'seller') {
        abort(403);
    }

    $product = Product::where('id', $id)
        ->where('seller_id', $user->id)
        ->firstOrFail();

    $validated = $request->validate([
        'name'     => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'price'    => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
    ]);

    // Tìm hoặc tạo category
    $category = Category::firstOrCreate(
        ['name' => mb_strtolower($validated['category'])],
        ['description' => null]
    );

    // Cập nhật sản phẩm
    $product->update([
        'name'        => $validated['name'],
        'category_id' => $category->id,
        'price'       => $validated['price'],
        'quantity'    => $validated['quantity'],
    ]);

    // 🔥 Đồng bộ tên sản phẩm trong order_items
    \App\Models\OrderItem::where('product_id', $product->id)
        ->update(['product_name' => $product->name]);

    return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công');
}


    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            abort(403);
        }

        $product = Product::where('id', $id)
            ->where('seller_id', $user->id)
            ->firstOrFail();

        // Xóa ảnh nếu có
        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $product->delete();

        return redirect()->back()->with('success', 'Xóa sản phẩm thành công');
    }
}
