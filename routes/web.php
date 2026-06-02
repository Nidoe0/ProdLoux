<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\StatisticsController;
use App\Http\Controllers\Admin\ReviewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) return view('admin.home');
    return match(auth()->user()->role) {
        'seller', 'admin' => redirect()->route('vendor.dashboard'),
        default           => redirect()->route('admin.home'),
    };
})->name('admin.home');

// Guests only
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password',   [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password',  [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',   [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Vendor / Admin authenticated
Route::middleware(['auth', 'role:seller,admin'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard',                              [DashboardController::class, 'index'])->name('dashboard');

    // Products (multi-image with Spatie)
    Route::get('/products',                               [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create',                        [ProductController::class, 'create'])->name('products.create');
    Route::post('/products',                              [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit',                [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}',                     [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}',                  [ProductController::class, 'destroy'])->name('products.destroy');

    // Orders
    Route::get('/orders',                                 [OrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/status/{status}',       [OrderController::class, 'updateStatus'])->name('orders.status');

    // Statistics + Excel export
    Route::get('/statistics',                             [StatisticsController::class, 'index'])->name('statistics');
    Route::get('/statistics/export',                      [StatisticsController::class, 'exportOrders'])->name('statistics.export');
});

// Admin — review moderation
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reviews',                  [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('/reviews/{review}/reject',  [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('/reviews/{review}',        [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// Logout
Route::post('/logout', function (\Illuminate\Http\Request $request) {

    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/login');

})->middleware('auth')->name('logout');
Route::get('/logout',  function () { Auth::logout(); return redirect('/'); });
