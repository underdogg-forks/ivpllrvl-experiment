<?php

use Illuminate\Support\Facades\Route;
use Modules\Payments\Controllers\PaymentsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('payments', PaymentsController::class)->names('payments');
});
