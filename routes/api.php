<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\PublicSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes for InstaDrop Courier Next.js Frontend
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::get('/settings/public', [PublicSettingsController::class, 'show']);
    Route::post('/inquiries', [InquiryController::class, 'store']);
    // Public Quote Submission from Next.js Frontend
    Route::post('/quotes', [QuoteController::class, 'store']);

    // Public Live Tracking Lookup by Tracking Number
    Route::get('/tracking/{tracking_number}', [OrderTrackingController::class, 'show']);

    // Public Invoice Details for Payment Checkout Page
    Route::get('/invoices/{token}', [PaymentController::class, 'show']);

    // Public Online Payment Processor
    Route::post('/payments/paypal/create', [PaymentController::class, 'createPayPalOrder']);
    Route::post('/payments/paypal/capture', [PaymentController::class, 'capturePayPalOrder']);
});
