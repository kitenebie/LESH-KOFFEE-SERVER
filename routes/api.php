<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderController;
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
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);

// Orders
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);

// Wallet
Route::get('/wallet', [WalletController::class, 'index']);
Route::post('/wallet/topup', [WalletController::class, 'topUp']);
Route::post('/wallet/debit', [WalletController::class, 'debit']);

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

// Promos
Route::get('/promos', [PromoController::class, 'index']);

// Subscriptions
Route::get('/subscriptions', [SubscriptionController::class, 'index']);

// Store
Route::get('/store', [StoreController::class, 'index']);

// User
Route::get('/user/profile', [UserController::class, 'profile']);
Route::put('/user/profile', [UserController::class, 'updateProfile']);
Route::get('/user/addresses', [UserController::class, 'addresses']);
Route::post('/user/addresses', [UserController::class, 'addAddress']);

// Vouchers
Route::get('/vouchers', [VoucherController::class, 'index']);
Route::get('/vouchers/unclaimed', [VoucherController::class, 'unclaimed']);
Route::get('/vouchers/claimed', [VoucherController::class, 'claimed']);
Route::post('/vouchers/{id}/claim', [VoucherController::class, 'claim']);

// Payments (BUX.ph)
Route::post('/payments/checkout', [PaymentController::class, 'checkout']);
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
Route::get('/payments/success', [PaymentController::class, 'success']);
Route::get('/payments/status/{reqId}', [PaymentController::class, 'status']);

// Ratings
Route::post('/ratings', [RatingController::class, 'store']);
Route::post('/ratings/order', [RatingController::class, 'rateOrder']);
Route::get('/ratings/product/{id}', [RatingController::class, 'productRatings']);
Route::get('/ratings/order/{orderId}', [RatingController::class, 'orderRatings']);
