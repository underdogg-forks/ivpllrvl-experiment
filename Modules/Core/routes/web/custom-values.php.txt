<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CustomValuesController;

Route::middleware('web')->group(function () {
    Route::get('custom-values', [CustomValuesController::class, 'index'])->name('custom-values.index');
    Route::get('custom-values/field', [CustomValuesController::class, 'field'])->name('custom-values.field');
    Route::get('custom-values/edit', [CustomValuesController::class, 'edit'])->name('custom-values.edit');
    Route::post('custom-values/create', [CustomValuesController::class, 'create'])->name('custom-values.create');
    Route::get('custom-values/delete', [CustomValuesController::class, 'delete'])->name('custom-values.delete');
});
