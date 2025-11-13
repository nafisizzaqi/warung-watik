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
    ProfileController,
    TestimonialController,
    MidtransController // <--- tambahin ini
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
        Route::post('/orders/{order}/shipment', [ShipmentController::class, 'createShipment']);
        Route::get('/shipments/couriers', [ShipmentController::class, 'getCourierOptions']);

        // Testimonials
        Route::get('/testimonials', [TestimonialController::class, 'index']);
        Route::post('/testimonials', [TestimonialController::class, 'store']);

        // 💳 Midtrans integration
        Route::post('orders/{id}/midtrans/snap-token', [MidtransController::class, 'createSnapToken'])
            ->name('customer.midtrans.snap');
            
    });

    // Public
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
});

// Midtrans callback (tidak perlu auth)
Route::post('/midtrans/callback', [MidtransController::class, 'handleCallback']);
Route::get('orders/{midtransOrderId}/midtrans/status', [MidtransController::class, 'checkStatus'])
    ->name('customer.midtrans.status')
    ->middleware('auth:customer');
