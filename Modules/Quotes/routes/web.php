<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotes\Controllers\QuotesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('quotes', QuotesController::class)->names('quotes');
});
