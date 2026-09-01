<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\PayPalController;
use App\Http\Controllers\Api\AnalyticsSettingController;

/*
|--------------------------------------------------------------------------
| API Routes for InstaDrop Courier Next.js Frontend & Admin Panel
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public System & Business Contact Settings
    Route::get('/settings', [SettingController::class, 'show']);
    Route::get('/analytics-settings', [AnalyticsSettingController::class, 'show']);

    // Public Quote Submission from Next.js Frontend
    Route::post('/quotes', [QuoteController::class, 'store']);

    // Public Live Tracking Lookup by Tracking Number
    Route::get('/tracking/{tracking_number}', [OrderTrackingController::class, 'show']);

    // Public Invoice Details for Payment Checkout Page
    Route::get('/invoices/{token}', [PaymentController::class, 'show']);

    // Public Online Payment Processors
    Route::post('/payments/process', [PaymentController::class, 'process']);
    Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
    Route::post('/paypal/capture-order', [PayPalController::class, 'captureOrder']);
});
