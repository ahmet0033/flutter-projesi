<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'redeast-api',
    'version' => 'v1',
]));

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);

Route::get('/shipping/methods', [CheckoutController::class, 'shippingMethods']);
Route::post('/coupons/validate', [CheckoutController::class, 'validateCoupon']);
Route::post('/checkout/quote', [CheckoutController::class, 'quote']);

Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart/items', [CartController::class, 'add']);
Route::put('/cart/items/{item}', [CartController::class, 'update']);
Route::delete('/cart/items/{item}', [CartController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'changePassword']);

    Route::post('/cart/merge', [CartController::class, 'merge']);
    Route::delete('/cart', [CartController::class, 'clear']);

    Route::apiResource('addresses', AddressController::class)->except(['show']);

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});
