<?php

use Illuminate\Support\Facades\Route;
use Modules\Expenses\Controllers\ExpensesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('expenses', ExpensesController::class)->names('expenses');
});
