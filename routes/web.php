<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PodController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes — InstaDrop Courier Admin Panel & Public Checkout
|--------------------------------------------------------------------------
*/

// Public Payment Checkout Page for Customer
Route::get('/pay/{token}', function ($token) {
    $invoiceData = (new PaymentController())->show($token)->getData(true);
    if (!isset($invoiceData['success']) || !$invoiceData['success']) {
        abort(404, 'Invalid or expired payment link');
    }
    return view('payment.checkout', ['invoice' => $invoiceData]);
})->name('payment.checkout');

// Admin Panel Routes
Route::prefix('admin')->as('admin.')->group(function () {
    // Dashboard Home
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quote Requests Module
    Route::get('/quotes', [QuoteRequestController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteRequestController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/status', [QuoteRequestController::class, 'updateStatus'])->name('quotes.status');

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
