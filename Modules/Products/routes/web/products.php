<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\ProductsAjaxController;
use Modules\Products\Controllers\ProductsController;

Route::middleware('web')->group(function () {
    Route::get('products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('products/form/{product_id?}', [ProductsController::class, 'form'])->name('products.form');
    Route::post('products/delete/{product_id}', [ProductsController::class, 'delete'])->name('products.delete');
    Route::get('products/modal-product-lookups', [ProductsAjaxController::class, 'modal_product_lookups'])->name('products.ajax.modal_product_lookups');
    Route::get('products/process-product-selections', [ProductsAjaxController::class, 'process_product_selections'])->name('products.ajax.process_product_selections');
});
