<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CustomFieldsController;

Route::middleware('web')->group(function () {
    Route::get('custom-fields', [CustomFieldsController::class, 'index'])->name('custom-fields.index');
    Route::get('custom-fields/table', [CustomFieldsController::class, 'table'])->name('custom-fields.table');
    Route::get('custom-fields/form', [CustomFieldsController::class, 'form'])->name('custom-fields.form');
    Route::get('custom-fields/delete', [CustomFieldsController::class, 'delete'])->name('custom-fields.delete');
});
