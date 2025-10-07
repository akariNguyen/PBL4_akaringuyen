<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminShopController;
use App\Http\Controllers\VoucherController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🏠 Trang chủ
Route::get('/', function () {
    return view('home', ['mode' => 'welcome']);
})->name('home');

// 🔐 Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ⭐ Review sản phẩm
Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth')->name('reviews.store');

// 👤 Dashboard cho từng loại user
Route::middleware('auth')->group(function () {
    Route::get('/customer/dashboard', function () {
        return view('RouteUser', ['user' => auth()->user(), 'role' => 'customer']);
    })->name('customer.dashboard');

    Route::get('/seller/dashboard', function () {
        return view('seller_dashboard'); // ✅ Trang dashboard riêng cho seller
    })->name('seller.dashboard');
});

// 🛍️ Products (seller only)
Route::middleware(['auth'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// ⚙️ Account pages
Route::middleware(['auth'])->group(function () {
    Route::get('/account/personal', [ProfileController::class, 'edit'])->name('account.personal');
    Route::post('/account/personal', [ProfileController::class, 'update'])->name('account.personal.update');
    Route::post('/account/password', [ProfileController::class, 'changePassword'])->name('account.password.update');
    Route::get('/account/shop', fn() => view('account_shop'))->name('account.shop');
    Route::post('/account/shop', [ShopController::class, 'updateAccount'])->name('account.shop.update');

    // 🏠 Address book
    Route::get('/account/addresses', [ProfileController::class, 'listAddresses'])->name('account.addresses.list');
    Route::post('/account/addresses', [ProfileController::class, 'addAddress'])->name('account.addresses.add');
    Route::post('/account/addresses/{id}/default', [ProfileController::class, 'setDefaultAddress'])->name('account.addresses.set_default');
    Route::delete('/account/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('account.addresses.delete');
});

// 🧾 Orders
Route::middleware(['auth'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/account/orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelByCustomer'])->name('orders.cancel');
});

// 🛒 Cart
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/my-cart', [CartController::class, 'myCart'])->name('cart.my');
});

// 🏪 Shops
Route::get('/shops/create', [ShopController::class, 'create'])->name('shops.create');
Route::post('/shops', [ShopController::class, 'store'])->name('shops.store');

// 💳 Checkout
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{productId}', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/from-cart', [CheckoutController::class, 'fromCart'])->name('checkout.fromCart');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
});

// 🧮 Admin (phần cũ)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class);
    Route::resource('products', AdminProductController::class);
    Route::resource('orders', AdminOrderController::class);
    Route::resource('shops', AdminShopController::class);

    Route::patch('users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::patch('shops/{id}/toggle-status', [AdminShopController::class, 'toggleStatus'])->name('shops.toggleStatus');
});


// 🧾✨ NHÓM MỚI — ROUTE DÀNH RIÊNG CHO SELLER VOUCHER
Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');     // 📋 Danh sách voucher
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create'); // ➕ Thêm voucher
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');        // 💾 Lưu voucher
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');  // ✏️ Cập nhật
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy'); // 🗑️ Xóa
    Route::get('/vouchers/json', [VoucherController::class, 'listJson'])->name('vouchers.json');  // 📡 JSON API
});
