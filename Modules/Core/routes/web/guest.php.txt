<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CustomerPortalController;
use Modules\Core\Controllers\Gateways\PaypalController;
use Modules\Core\Controllers\Gateways\StripeController;
use Modules\Core\Controllers\GetController;
use Modules\Core\Controllers\InvoicesController;
use Modules\Core\Controllers\PaymentInformation;
use Modules\Core\Controllers\PaymentsController;
use Modules\Core\Controllers\QuotesController;
use Modules\Core\Controllers\View;

Route::middleware('web')->group(function () {
    Route::get('guest', [InvoicesController::class, 'index'])->name('guest.index');
    Route::get('guest/status', [InvoicesController::class, 'status'])->name('guest.status');
    Route::get('guest/view', [InvoicesController::class, 'view'])->name('guest.view');
    Route::get('guest/generate-pdf', [InvoicesController::class, 'generatePdf'])->name('guest.generate-pdf');
    Route::get('guest/generate-sumex-pdf', [InvoicesController::class, 'generateSumexPdf'])->name('guest.generate-sumex-pdf');
    Route::get('guest', [CustomerPortalController::class, 'index'])->name('guest.index');
    Route::get('guest', [QuotesController::class, 'index'])->name('guest.index');
    Route::get('guest/status', [QuotesController::class, 'status'])->name('guest.status');
    Route::get('guest/view', [QuotesController::class, 'view'])->name('guest.view');
    Route::get('guest/generate-pdf', [QuotesController::class, 'generatePdf'])->name('guest.generate-pdf');
    Route::get('guest/approve', [QuotesController::class, 'approve'])->name('guest.approve');
    Route::get('guest/reject', [QuotesController::class, 'reject'])->name('guest.reject');
    Route::get('guest', [PaymentsController::class, 'index'])->name('guest.index');
    Route::get('guest/invoice', [View::class, 'invoice'])->name('guest.invoice');
    Route::get('guest/generate-invoice-pdf', [View::class, 'generateInvoicePdf'])->name('guest.generate-invoice-pdf');
    Route::get('guest/generate-sumex-pdf', [View::class, 'generateSumexPdf'])->name('guest.generate-sumex-pdf');
    Route::get('guest/quote', [View::class, 'quote'])->name('guest.quote');
    Route::get('guest/generate-quote-pdf', [View::class, 'generateQuotePdf'])->name('guest.generate-quote-pdf');
    Route::get('guest/approve-quote', [View::class, 'approveQuote'])->name('guest.approve-quote');
    Route::get('guest/reject-quote', [View::class, 'rejectQuote'])->name('guest.reject-quote');
    Route::get('guest/create-checkout-session', [StripeController::class, 'createCheckoutSession'])->name('guest.create-checkout-session');
    Route::get('guest/callback', [StripeController::class, 'callback'])->name('guest.callback');
    Route::get('guest/paypal-create-order', [PaypalController::class, 'paypalCreateOrder'])->name('guest.paypal-create-order');
    Route::get('guest/paypal-capture-payment', [PaypalController::class, 'paypalCapturePayment'])->name('guest.paypal-capture-payment');
    Route::get('guest/form', [PaymentInformation::class, 'form'])->name('guest.form');
    Route::get('guest/stripe', [PaymentInformation::class, 'stripe'])->name('guest.stripe');
    Route::get('guest/paypal', [PaymentInformation::class, 'paypal'])->name('guest.paypal');
    Route::get('guest/show-files', [GetController::class, 'showFiles'])->name('guest.show-files');
    Route::get('guest/get-file', [GetController::class, 'getFile'])->name('guest.get-file');
});
