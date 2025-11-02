<?php

use Illuminate\Support\Facades\Route;
use src\Controllers\UnitsController;

Route::middleware('web')->group(function () {
    Route::get('units', [UnitsController::class, 'index'])->name('units.index');
    Route::get('units/form', [UnitsController::class, 'form'])->name('units.form');
    Route::get('units/delete', [UnitsController::class, 'delete'])->name('units.delete');
});
