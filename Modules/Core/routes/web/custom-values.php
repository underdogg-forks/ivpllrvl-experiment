<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CustomValuesController;

Route::middleware('web')->group(function () {
    Route::query()->get('custom-values', [CustomValuesController::class, 'index'])->name('custom-values.index');
    Route::query()->get('custom-values/field', [CustomValuesController::class, 'field'])->name('custom-values.field');
    Route::query()->get('custom-values/edit', [CustomValuesController::class, 'edit'])->name('custom-values.edit');
    Route::post('custom-values/create', [CustomValuesController::class, 'create'])->name('custom-values.create');
    Route::query()->get('custom-values/delete', [CustomValuesController::class, 'delete'])->name('custom-values.delete');
});
