<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Controllers\PaymentMethodsController;
use Modules\Payments\Controllers\PaymentsController;

/*
|--------------------------------------------------------------------------
| Payments Web Routes
|--------------------------------------------------------------------------
*/

// Index routes
Route::get('/payments/index', [PaymentsController::class, 'index'])->name('payments.index');
Route::get('/payment_methods/index', [PaymentMethodsController::class, 'index'])->name('payment_methods.index');

// Form routes (GET to display form)
Route::get('/payments/form', [PaymentsController::class, 'form'])->name('payments.form');
Route::get('/payments/form/{id}', [PaymentsController::class, 'form']);
Route::get('/payment_methods/form', [PaymentMethodsController::class, 'form'])->name('payment_methods.form');
Route::get('/payment_methods/form/{id}', [PaymentMethodsController::class, 'form']);

// POST routes for create/update
Route::post('/payments/form', [PaymentsController::class, 'form']);
Route::post('/payments/form/{id}', [PaymentsController::class, 'form']);
Route::post('/payment_methods/form', [PaymentMethodsController::class, 'form']);
Route::post('/payment_methods/form/{id}', [PaymentMethodsController::class, 'form']);

// Delete routes (POST for safety)
Route::post('/payments/delete/{id}', [PaymentsController::class, 'delete'])->name('payments.delete');
Route::post('/payment_methods/delete/{id}', [PaymentMethodsController::class, 'delete'])->name('payment_methods.delete');

// AJAX routes
Route::get('/payments/online_logs', [PaymentsController::class, 'onlineLogs'])->name('payments.online_logs');
