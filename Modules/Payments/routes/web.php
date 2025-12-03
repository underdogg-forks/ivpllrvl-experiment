<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Controllers\PaymentsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('payments', PaymentsController::class)->names('payments');
});
