<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //chat messages routes
    Route::get('/chat',[ChatController::class,'index'])->name('chat');
    Route::post('/chat/send',[ChatController::class,'send'])->name('chat.send');
    Route::get('/chat/{id}', [ChatController::class,'load'])->name('chat.load');

    // POS
    Route::prefix('pos')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('pos.index');
        Route::post('/checkout', [PosController::class, 'store'])->name('pos.store');
        Route::get('/sales', [PosController::class, 'sales'])->name('pos.sales');
        Route::get('/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
    });

    // Admin / management
    Route::prefix('admin')->name('pos.')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('payments', PaymentController::class)->except(['show']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
    });

});

require __DIR__.'/auth.php';
