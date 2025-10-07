<?php
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminShopController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminProductAnalyticsController;
// Trang chủ
Route::get('/', function () {
    return view('home', ['mode' => 'welcome']);
})->name('home');

// Đăng nhập / Đăng ký / Đăng xuất
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Review sản phẩm
Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth')->name('reviews.store');

// Dashboard cho các loại user
Route::middleware('auth')->group(function () {
    Route::get('/customer/dashboard', function () {
        return view('RouteUser', ['user' => auth()->user(), 'role' => 'customer']);
    })->name('customer.dashboard');

    Route::get('/seller/dashboard', function () {
        return view('seller_dashboard');
    })->name('seller.dashboard');
});

// Products (seller only)
Route::middleware(['auth'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// Account pages
Route::middleware(['auth'])->group(function () {
    Route::get('/account/personal', [ProfileController::class, 'edit'])->name('account.personal');
    Route::post('/account/personal', [ProfileController::class, 'update'])->name('account.personal.update');
    Route::post('/account/password', [ProfileController::class, 'changePassword'])->name('account.password.update');
    Route::get('/account/shop', fn() => view('account_shop'))->name('account.shop');
    Route::post('/account/shop', [ShopController::class, 'updateAccount'])->name('account.shop.update');

    // Address book
    Route::get('/account/addresses', [ProfileController::class, 'listAddresses'])->name('account.addresses.list');
    Route::post('/account/addresses', [ProfileController::class, 'addAddress'])->name('account.addresses.add');
    Route::post('/account/addresses/{id}/default', [ProfileController::class, 'setDefaultAddress'])->name('account.addresses.set_default');
    Route::delete('/account/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('account.addresses.delete');
});

// Orders
Route::middleware(['auth'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/account/orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelByCustomer'])->name('orders.cancel');
});

// Cart
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{productId}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/my-cart', [CartController::class, 'myCart'])->name('cart.my');
});

// Shops
Route::get('/shops/create', [ShopController::class, 'create'])->name('shops.create');
Route::post('/shops', [ShopController::class, 'store'])->name('shops.store');

// Checkout
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{productId}', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/from-cart', [CheckoutController::class, 'fromCart'])->name('checkout.fromCart');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Phần mới - THAY THẾ
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // 📊 Dashboard chính
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 👥 Quản lý người dùng
    Route::resource('users', AdminUserController::class);
    Route::patch('users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])
        ->name('users.toggleStatus');
    
    // 🏬 Quản lý shop
    Route::get('shops/pending', [AdminShopController::class, 'pending'])->name('shops.pending');
    Route::patch('shops/{id}/approve', [AdminShopController::class, 'approve'])->name('shops.approve');
    Route::patch('shops/{id}/reject', [AdminShopController::class, 'reject'])->name('shops.reject');
    Route::patch('shops/{id}/toggle-status', [AdminShopController::class, 'toggleStatus'])->name('shops.toggleStatus');
    Route::get('shops/{id}/detail', [AdminShopController::class, 'showDetail'])->name('shops.detail');
    Route::resource('shops', AdminShopController::class);

    // 📦 Quản lý sản phẩm
    Route::get('products/in-stock', [AdminProductController::class, 'inStock'])->name('products.inStock');
    Route::get('products/pending', [AdminProductController::class, 'pending'])->name('products.pending');
    Route::patch('products/{id}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::patch('products/{id}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
    Route::resource('products', AdminProductController::class);

    // 🧾 Quản lý đơn hàng
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{id}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // 📈 Phân tích doanh thu
    Route::get('analytics', [\App\Http\Controllers\Admin\AdminAnalyticsController::class, 'index'])
        ->name('analytics');
    Route::get('analytics/weeks', [\App\Http\Controllers\Admin\AdminAnalyticsController::class, 'getWeeks'])
        ->name('analytics.weeks');

    // 🧮 Phân tích sản phẩm
    Route::get('analytics/products', [\App\Http\Controllers\Admin\AdminProductAnalyticsController::class, 'index'])
        ->name('analytics.products');
});

