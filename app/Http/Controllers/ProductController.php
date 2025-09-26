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
        if (!$user || $user->role !== 'seller') {
            abort(403);
        }
        return view('product_create');
    }

    public function show($id)
{
    $product = Product::with('reviews.user')->findOrFail($id);

    $avgRating = $product->reviews()->avg('rating');
    $reviews = $product->reviews()->latest()->get();

    $variations = [ /* ... như bạn để ... */ ];

    return view('product_show', compact('product', 'variations', 'avgRating', 'reviews'));
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
            'status'      => 'required|in:in_stock,out_of_stock,discontinued,pending',
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
            'status'      => $validated['status'],
        ]);

        if ($request->hasFile('images')) {
            $stored = [];
            foreach ($request->file('images') as $file) {
                $stored[] = $file->store('products', 'public');
            }
            $product->images = $stored;
            $product->save();
        }

        return redirect()->route('seller.dashboard')->with('success', 'Tạo sản phẩm thành công');
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
