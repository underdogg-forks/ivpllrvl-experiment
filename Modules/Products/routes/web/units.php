<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\UnitsController;

Route::middleware('web')->group(function () {
    Route::get('units', [UnitsController::class, 'index'])->name('units.index');
    Route::get('units/form/{unit_id?}', [UnitsController::class, 'form'])->name('units.form');
    Route::post('units/delete/{unit_id}', [UnitsController::class, 'delete'])->name('units.delete');
});
