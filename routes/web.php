<?php

use App\Http\Controllers\Admin\AnalyticsSettingController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CoverageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PodController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\SEO\LlmsController;
use App\Http\Controllers\SEO\RobotsController;
use App\Http\Controllers\SEO\SitemapController;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [RobotsController::class, 'index']);
Route::get('/llms.txt', [LlmsController::class, 'index']);
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::get('/', fn () => redirect()->route('admin.dashboard'));

Route::get('/pay/{token}', function (string $token) {
    abort_unless(Invoice::where('payment_token', $token)->exists(), 404);

    return redirect()->away(rtrim(config('app.frontend_url'), '/').'/pay/'.rawurlencode($token));
})->name('payment.checkout');

Route::middleware('guest')->prefix('admin')->as('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->prefix('admin')->as('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/security', [AuthController::class, 'edit'])->name('security.edit');
    Route::put('/security', [AuthController::class, 'update'])->middleware('throttle:5,1')->name('security.update');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/quotes', [QuoteRequestController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteRequestController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/status', [QuoteRequestController::class, 'updateStatus'])->name('quotes.status');
    Route::post('/quotes/{quote}/invoice', [InvoiceController::class, 'generate'])->name('invoices.generate');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{order}/pod', [PodController::class, 'create'])->name('pods.create');
    Route::post('/orders/{order}/pod', [PodController::class, 'store'])->name('pods.store');

    Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::post('/inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.status');

    Route::post('/blogs/upload', [BlogController::class, 'upload'])->name('blogs.upload');
    Route::resource('blogs', BlogController::class)->except(['show']);

    Route::get('/services/directory', [ServiceController::class, 'editDirectory'])->name('services.directory.edit');
    Route::put('/services/directory', [ServiceController::class, 'updateDirectory'])->name('services.directory.update');
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::get('/coverage', [CoverageController::class, 'edit'])->name('coverage.edit');
    Route::put('/coverage', [CoverageController::class, 'update'])->name('coverage.update');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('/analytics', [AdminAnalyticsController::class, 'update'])->name('analytics.update');
});
