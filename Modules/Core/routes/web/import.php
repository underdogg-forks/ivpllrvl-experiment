<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\ImportController;

Route::middleware('web')->group(function () {
    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::get('import/form', [ImportController::class, 'form'])->name('import.form');
    Route::post('import/delete/{import_id}', [ImportController::class, 'delete'])->name('import.delete');
});
