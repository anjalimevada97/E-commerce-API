<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('products', ProductController::class);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add-item', [CartController::class, 'addCartItem']);
    Route::post('/cart/remove', [CartController::class, 'remove']); // decrement quantity
    Route::delete('/cart/cart-items/{cart_item}', [CartController::class, 'removeCartItem']); // remove product completely

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/checkout', [OrderController::class, 'checkout']);

    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);
});
