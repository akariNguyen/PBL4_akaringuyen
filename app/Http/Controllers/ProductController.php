<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $product = Product::findOrFail($id);
        // Giả sử có variations (nếu chưa có model, có thể hardcode hoặc thêm sau)
        $variations = ['sết 380G', 'SET BƠ BƠ', 'SET 380G + SỐT TÁC', 'SẾT NĂNG KG', 'SẾT NHIEU BÁNH', 'y hinh[50g bo]', 'set siêu bơ[muối béo]', 'set năng kg + sốt tắc', 'set 380g[muối béo]', 'set siêu bơ[muối béo]', 'set 350G'];

        return view('product_show', compact('product', 'variations'));
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:in_stock,out_of_stock,discontinued,pending',
            'images.*' => 'nullable|image|max:4096',
        ]);

        // Find or create category by name (case-insensitive)
        $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($validated['category'])])->first();
        if (!$category) {
            $category = Category::create([
                'name' => $validated['category'],
                'description' => null,
            ]);
        }

        $product = Product::create([
            'seller_id' => $user->id,
            'category_id' => $category->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'status' => $validated['status'],
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
}


