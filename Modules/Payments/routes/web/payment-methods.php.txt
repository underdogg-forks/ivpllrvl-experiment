<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\app\Http\Controllers\PaymentMethodsController;

Route::middleware('web')->group(function () {
    Route::get('payment-methods', [PaymentMethodsController::class, 'index'])->name('payment-methods.index');
    Route::get('payment-methods/form', [PaymentMethodsController::class, 'form'])->name('payment-methods.form');
    Route::get('payment-methods/delete', [PaymentMethodsController::class, 'delete'])->name('payment-methods.delete');
});
