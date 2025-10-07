<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $products = Product::with(['category', 'seller'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Hiển thị form tạo sản phẩm
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Lưu sản phẩm mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'status'      => 'required|in:pending,in_stock,out_of_stock,discontinued',
            'description' => 'nullable|string',
        ]);

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm thành công!');
    }

    /**
     * Hiển thị chi tiết sản phẩm
     */
    public function show($id)
    {
        $product = Product::with(['category', 'seller'])->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'status'      => 'required|in:pending,in_stock,out_of_stock,discontinued',
            'description' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công!');
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công!');
    }
    public function inStock(Request $request)
{
    $query = Product::with(['seller.shop', 'category'])
        ->where('status', 'in_stock')
        ->whereHas('seller.shop', fn($q) => $q->where('status', 'active'));

    // 🧠 Smart Search
    if ($search = $request->input('q')) {
        $query->where(function ($q) use ($search) {
            // Ưu tiên: khớp tên sản phẩm trước
            $q->where('name', 'like', "%{$search}%")
              // Rồi tới shop có tên gần giống
              ->orWhereHas('seller.shop', function ($shopQuery) use ($search) {
                  $shopQuery->where('name', 'like', "%{$search}%");
              });
        })
        // 👉 Sắp xếp ưu tiên sản phẩm trùng tên trước
        ->orderByRaw("
            CASE
                WHEN name LIKE ? THEN 1
                WHEN name LIKE ? THEN 2
                ELSE 3
            END
        ", ["{$search}", "%{$search}%"]);
    }

    // Lọc theo danh mục nếu có
    if ($category = $request->input('category')) {
        $query->where('category_id', $category);
    }

    $categories = \App\Models\Category::all();
    $products = $query->latest()->get();

    return view('admin.products_in_stock', compact('products', 'categories'));
}

    public function pending()
{
    $products = \App\Models\Product::where('status', 'pending')
        ->with(['seller.shop', 'category'])
        ->latest()
        ->get();

    return view('admin.products_pending', compact('products'));
}

public function approve($id)
{
    $product = \App\Models\Product::findOrFail($id);
    $product->status = 'in_stock';
    $product->save();

    return back()->with('success', '✅ Sản phẩm đã được duyệt và chuyển sang trạng thái "in_stock".');
}

public function reject($id)
{
    $product = \App\Models\Product::findOrFail($id);
    $product->status = 'rejected';
    $product->save();

    return back()->with('error', '❌ Sản phẩm đã bị từ chối.');
}



}
