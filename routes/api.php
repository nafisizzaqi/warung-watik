<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\{
    RegisterController,
    LoginController,
    LogoutController,
    CategoryController,
    ProductController,
    CartController,
    OrderController,
    PaymentController,
    ShipmentController,
    ProfileController
};

Route::prefix('customer')->group(function () {
    // Auth
    Route::post('register', RegisterController::class)->name('customer.register');
    Route::post('login', LoginController::class)->name('customer.login');

    // Protected routes
    Route::middleware('auth:customer')->group(function () {
        Route::post('logout', LogoutController::class)->name('customer.logout');

        Route::get('profile', [ProfileController::class, 'show'])->name('customer.profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('customer.profile.update');

        // Cart
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart/items', [CartController::class, 'store']);
        Route::put('cart/items/{id}', [CartController::class, 'update']);
        Route::delete('cart/items/{id}', [CartController::class, 'destroy']);

        // Orders
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{id}', [OrderController::class, 'show']);

        // Payments
        Route::post('orders/{id}/payment', [PaymentController::class, 'store']);
        Route::get('orders/{id}/payment', [PaymentController::class, 'show']);

        // Shipments
        Route::get('orders/{id}/shipment', [ShipmentController::class, 'show']);
    });

    // Public
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
});
