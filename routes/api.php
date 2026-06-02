<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\VendorProductController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Stripe Webhook (no auth — Stripe sends raw POST) ─────────────────────────
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
     ->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class]);

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ── Public catalogue (no auth required) ──────────────────────────────────────
Route::get('/categories',                     [ProductController::class, 'categories']);
Route::get('/products',                       [ProductController::class, 'index']);
Route::get('/products/{product}',             [ProductController::class, 'show']);
Route::get('/products/{product}/reviews',     [ReviewController::class, 'index']);

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Notifications (all authenticated users)
    Route::prefix('notifications')->group(function () {
        Route::get('/',               [NotificationController::class, 'index']);
        Route::get('/unread-count',   [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read',     [NotificationController::class, 'markRead']);
        Route::post('/read-all',      [NotificationController::class, 'markAllRead']);
    });

    // Reviews — submit & flag (all authenticated)
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/{review}/flag',      [ReviewController::class, 'flag']);

    // ── Buyer only ────────────────────────────────────────────────────────────
    Route::middleware('role:buyer')->group(function () {
        // Cart
        Route::get('/cart',              [CartController::class, 'index']);
        Route::post('/cart',             [CartController::class, 'add']);
        Route::put('/cart/{cart}',       [CartController::class, 'update']);
        Route::delete('/cart/{cart}',    [CartController::class, 'remove']);
        Route::delete('/cart',           [CartController::class, 'clear']);

        // Orders
        Route::get('/orders',                              [OrderController::class, 'index']);
        Route::post('/orders',                             [OrderController::class, 'store']);
        Route::post('/orders/{order}/payment-intent',     [OrderController::class, 'createPaymentIntent']);
        Route::post('/orders/{order}/confirm-payment',    [OrderController::class, 'confirmPayment']);
    });

    // ── Seller & Admin — vendor product management (API) ─────────────────────
    Route::middleware('role:seller,admin')->prefix('vendor')->group(function () {
        Route::get('/products',              [VendorProductController::class, 'index']);
        Route::post('/products',             [VendorProductController::class, 'store']);
        Route::get('/products/{product}',    [VendorProductController::class, 'show']);
        Route::put('/products/{product}',    [VendorProductController::class, 'update']);
        Route::delete('/products/{product}', [VendorProductController::class, 'destroy']);
    });
});
