<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\SaleApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthApiController::class, 'register'])->middleware('throttle:5,1');

Route::middleware('auth.token')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    // Products
    Route::get('/products', [ProductApiController::class, 'index']);
    Route::post('/products', [ProductApiController::class, 'store']);
    Route::put('/products/{product}', [ProductApiController::class, 'update']);
    Route::delete('/products/{product}', [ProductApiController::class, 'destroy']);

    // Sales
    Route::get('/sales', [SaleApiController::class, 'index']);
    Route::post('/checkout', [SaleApiController::class, 'checkout'])->middleware('throttle:10,1');

    // Payments
    Route::get('/payments', [PaymentApiController::class, 'index']);
    Route::post('/payments', [PaymentApiController::class, 'store']);
    Route::put('/payments/{payment}', [PaymentApiController::class, 'update']);
    Route::delete('/payments/{payment}', [PaymentApiController::class, 'destroy']);

    // Reports
    Route::get('/reports', [ReportApiController::class, 'index']);
});