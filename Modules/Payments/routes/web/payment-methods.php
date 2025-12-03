<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Controllers\PaymentMethodsController;

Route::middleware('web')->group(function () {
    Route::get('payment-methods', [PaymentMethodsController::class, 'index'])->name('payment-methods.index');
    Route::get('payment-methods/form/{payment_method_id?}', [PaymentMethodsController::class, 'form'])->name('payment-methods.form');
    Route::post('payment-methods/delete/{payment_method_id}', [PaymentMethodsController::class, 'delete'])->name('payment-methods.delete');
});
