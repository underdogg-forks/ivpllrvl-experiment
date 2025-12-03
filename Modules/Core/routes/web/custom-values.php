<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CustomValuesController;

Route::middleware('web')->group(function () {
    Route::get('custom-values', [CustomValuesController::class, 'index'])->name('custom-values.index');
    Route::get('custom-values/field/{custom_field_id?}', [CustomValuesController::class, 'field'])->name('custom-values.field');
    Route::get('custom-values/edit/{custom_values_id?}', [CustomValuesController::class, 'edit'])->name('custom-values.edit');
    Route::post('custom-values/create/{custom_field_id?}', [CustomValuesController::class, 'create'])->name('custom-values.create');
    Route::post('custom-values/delete/{custom_values_id}', [CustomValuesController::class, 'delete'])->name('custom-values.delete');
});
