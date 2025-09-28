<?php
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;

// Trang chủ
Route::get('/', function () {
    return view('home', ['mode' => 'welcome']);
})->name('home');

// Routes cho đăng nhập
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Routes cho đăng ký
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Route đăng xuất
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');

// Routes cho dashboard (cần đăng nhập)
Route::middleware('auth')->group(function () {
    Route::get('/customer/dashboard', function () {
        return view('RouteUser', ['user' => auth()->user(), 'role' => 'customer']);
    })->name('customer.dashboard');
    
    Route::get('/seller/dashboard', function () {
        return view('seller_dashboard');
    })->name('seller.dashboard');
    
    Route::get('/admin/dashboard', function () {
        return view('RouteUser', ['user' => auth()->user(), 'role' => 'admin']);
    })->name('admin.dashboard');
});

// Products (seller only)
Route::middleware(['auth'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});


// Account pages
Route::middleware(['auth'])->group(function () {
    Route::get('/account/personal', [ProfileController::class, 'edit'])->name('account.personal');
    Route::post('/account/personal', [ProfileController::class, 'update'])->name('account.personal.update');
    Route::post('/account/password', [ProfileController::class, 'changePassword'])->name('account.password.update');
    Route::get('/account/shop', function () { return view('account_shop'); })->name('account.shop');
    Route::post('/account/shop', [ShopController::class, 'updateAccount'])->name('account.shop.update');
    // Address book APIs
    Route::get('/account/addresses', [ProfileController::class, 'listAddresses'])->name('account.addresses.list');
    Route::post('/account/addresses', [ProfileController::class, 'addAddress'])->name('account.addresses.add');
    Route::post('/account/addresses/{id}/default', [ProfileController::class, 'setDefaultAddress'])->name('account.addresses.set_default');
    Route::delete('/account/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('account.addresses.delete');
});

// Products (seller only)
Route::middleware(['auth'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
});

// Orders
Route::middleware(['auth'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/account/orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->middleware('auth');

});

Route::middleware(['auth'])->group(function () {
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Người mua hủy đơn hàng của chính mình (chỉ khi đang chờ xử lý)
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelByCustomer'])->name('orders.cancel');

});

use App\Http\Controllers\CartController;

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');

    // 👇 Route mới
    Route::get('/my-cart', [CartController::class, 'myCart'])->name('cart.my');
});



Route::get('/shops/create', [ShopController::class, 'create'])->name('shops.create');
Route::post('/shops', [ShopController::class, 'store'])->name('shops.store');

Route::middleware('auth')->group(function () {
    // Thanh toán 1 sản phẩm (Buy Now)
    Route::get('/checkout/{productId}', [CheckoutController::class, 'index'])->name('checkout');

    // Thanh toán nhiều sản phẩm từ giỏ (SỬA LẠI)
    Route::post('/checkout/from-cart', [CheckoutController::class, 'fromCart'])->name('checkout.fromCart');

    // Xác nhận đặt hàng
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
});