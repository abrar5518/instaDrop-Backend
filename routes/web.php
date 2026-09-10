<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PodController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Admin\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes — InstaDrop Courier Admin Panel & Public Checkout
|--------------------------------------------------------------------------
*/

// Public Payment Checkout Page for Customer
Route::get('/pay/{token}', function ($token) {
    abort_unless(\App\Models\Invoice::where('payment_token', $token)->exists(), 404);
    return redirect()->away(rtrim(config('app.frontend_url'), '/') . '/pay/' . rawurlencode($token));
})->name('payment.checkout');

// Admin Panel Routes
Route::middleware('guest')->prefix('admin')->as('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::middleware('auth')->prefix('admin')->as('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/security', [AuthController::class, 'edit'])->name('security.edit');
    Route::put('/security', [AuthController::class, 'update'])->middleware('throttle:5,1')->name('security.update');
    Route::post('/blogs/upload', [\App\Http\Controllers\Admin\BlogController::class, 'upload'])->name('blogs.upload');
    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class)->except(['show']);

    // Dashboard Home
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quote Requests Module
    Route::get('/quotes', [QuoteRequestController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteRequestController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/status', [QuoteRequestController::class, 'updateStatus'])->name('quotes.status');

    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::post('/inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.status');

    // Quotation Pricing & Invoice Generation Action
    Route::post('/quotes/{quote}/invoice', [InvoiceController::class, 'generate'])->name('invoices.generate');

    // Orders & Delivery Status Manager
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    // POD Upload Module
    Route::get('/orders/{order}/pod', [PodController::class, 'create'])->name('pods.create');
    Route::post('/orders/{order}/pod', [PodController::class, 'store'])->name('pods.store');

    // System Settings Module
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
