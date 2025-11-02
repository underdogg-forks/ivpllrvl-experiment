<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Controllers\PaymentsAjaxController;
use Modules\Payments\Controllers\PaymentsController;

Route::middleware('web')->group(function () {
    Route::get('payments/add', [PaymentsAjaxController::class, 'add'])->name('payments.add');
    Route::get('payments/modal-add-payment', [PaymentsAjaxController::class, 'modalAddPayment'])->name('payments.modal-add-payment');
    Route::get('payments', [PaymentsController::class, 'index'])->name('payments.index');
    Route::get('payments/form', [PaymentsController::class, 'form'])->name('payments.form');
    Route::get('payments/online-logs', [PaymentsController::class, 'onlineLogs'])->name('payments.online-logs');
    Route::get('payments/delete', [PaymentsController::class, 'delete'])->name('payments.delete');
});
