<?php

use Illuminate\Support\Facades\Route;
use Modules\Expenses\Controllers\ExpensesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('expenses', ExpensesController::class)->names('expenses');
});
