<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\ProductsAjaxController;
use Modules\Products\Controllers\ProductsController;

Route::middleware('web')->group(function () {
    Route::get('products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('products/form', [ProductsController::class, 'form'])->name('products.form');
    Route::get('products/delete', [ProductsController::class, 'delete'])->name('products.delete');
    Route::get('products/modal-product-lookups', [ProductsAjaxController::class, 'modalProductLookups'])->name('products.modal-product-lookups');
    Route::get('products/process-product-selections', [ProductsAjaxController::class, 'processProductSelections'])->name('products.process-product-selections');
});
