<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\ProductsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('products', ProductsController::class)->names('products');
});
