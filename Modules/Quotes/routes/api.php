<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotes\Controllers\QuotesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('quotes', QuotesController::class)->names('quotes');
});
