<?php

use App\Http\Controllers\Api\AnalyticsSettingController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PublicSettingsController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/settings', [SettingController::class, 'show']);
    Route::get('/settings/public', [PublicSettingsController::class, 'show']);
    Route::get('/analytics-settings', [AnalyticsSettingController::class, 'show']);

    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);
    Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/quotes', [QuoteController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/tracking/{tracking_number}', [OrderTrackingController::class, 'show'])->middleware('throttle:30,1');

    Route::get('/invoices/{token}', [PaymentController::class, 'show']);
    Route::post('/payments/paypal/create', [PaymentController::class, 'createPayPalOrder'])->middleware('throttle:10,1');
    Route::post('/payments/paypal/capture', [PaymentController::class, 'capturePayPalOrder'])->middleware('throttle:10,1');
});
