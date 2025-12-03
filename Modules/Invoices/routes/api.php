<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Controllers\InvoicesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('invoices', InvoicesController::class)->names('invoices');
});
