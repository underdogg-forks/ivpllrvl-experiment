<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\FamiliesController;

Route::middleware('web')->group(function () {
    Route::get('families', [FamiliesController::class, 'index'])->name('families.index');
    Route::get('families/form/{family_id?}', [FamiliesController::class, 'form'])->name('families.form');
    Route::post('families/delete/{family_id}', [FamiliesController::class, 'delete'])->name('families.delete');
});
