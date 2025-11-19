<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\TaxRatesController;

Route::middleware('web')->group(function () {
    Route::get('tax-rates', [TaxRatesController::class, 'index'])->name('tax-rates.index');
    Route::get('tax-rates/form', [TaxRatesController::class, 'form'])->name('tax-rates.form');
    Route::post('tax-rates/form', [TaxRatesController::class, 'formStore'])->name('tax-rates.formStore');
    Route::post('tax-rates/delete', [TaxRatesController::class, 'delete'])->name('tax-rates.delete');
});
