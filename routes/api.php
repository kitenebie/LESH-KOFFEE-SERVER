<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\MembershipCardController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StampController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\RatingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes are organized into:
| 1. PUBLIC routes — no auth required (login, register, public catalog)
| 2. PROTECTED routes — require auth:sanctum (user data, wallet, orders)
| 3. WEBHOOK routes — server-to-server only (payment callbacks)
|
*/

// ─── PUBLIC ROUTES (no auth required) ─────────────────────────────────────────

// Auth (login/register are public; throttled to prevent brute force)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

// OTP endpoints (public, throttled separately to prevent abuse)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/otp/send', [OtpController::class, 'send']);
    Route::post('/otp/verify', [OtpController::class, 'verify']);
    Route::post('/otp/resend', [OtpController::class, 'resend']);
});

// Public catalog (read-only, no user context needed)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/promos', [PromoController::class, 'index']);
Route::get('/subscriptions', [SubscriptionController::class, 'index']);
Route::get('/store', [StoreController::class, 'index']);

// ─── PROTECTED ROUTES (require valid Sanctum token) ───────────────────────────

Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifyRequestSignature::class, 'throttle:600,1'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/resume', [AuthController::class, 'resume']);

    // User Profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::get('/user/addresses', [UserController::class, 'addresses']);
    Route::post('/user/addresses', [UserController::class, 'addAddress']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Wallet (read balance + debit only — top-up is done via webhook)
    Route::get('/wallet', [WalletController::class, 'index']);
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/wallet/debit', [WalletController::class, 'debit']);
        Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
    });

    // Loyalty
    Route::get('/loyalty/transactions', [LoyaltyController::class, 'index']);
    Route::get('/loyalty/points', [LoyaltyController::class, 'points']);
    Route::post('/loyalty/earn', [LoyaltyController::class, 'earn']);
    Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem']);
    Route::post('/loyalty/recalculate', [LoyaltyController::class, 'recalculate']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Stamps
    Route::get('/stamps', [StampController::class, 'index']);
    Route::get('/stamps/quota-progress', [StampController::class, 'quotaProgress']);

    // Membership Card
    Route::get('/membership-card', [MembershipCardController::class, 'index']);

    // Subscriptions (user actions)
    Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/subscriptions/my', [SubscriptionController::class, 'mySubscriptions']);

    // Vouchers
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::get('/vouchers/unclaimed', [VoucherController::class, 'unclaimed']);
    Route::get('/vouchers/claimed', [VoucherController::class, 'claimed']);
Route::post('/vouchers/{id}/claim', [VoucherController::class, 'claim']);
Route::post('/vouchers/claim-by-code', [VoucherController::class, 'claimByCode']);

    // Payments (initiate checkout — requires authenticated user)
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/payments/checkout', [PaymentController::class, 'checkout']);
    });
    Route::get('/payments/status/{reqId}', [PaymentController::class, 'status']);

    // Ratings
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::post('/ratings/order', [RatingController::class, 'rateOrder']);
    Route::get('/ratings/product/{id}', [RatingController::class, 'productRatings']);
    Route::get('/ratings/order/{orderId}', [RatingController::class, 'orderRatings']);
});

// ─── WEBHOOK ROUTES (server-to-server, no user auth) ──────────────────────────
// BUX.ph payment webhook — verified by signature, not by user token.
// Rate limited to prevent abuse. No IdentifyUser middleware needed.
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
    Route::get('/payments/success', [PaymentController::class, 'success']);
});
