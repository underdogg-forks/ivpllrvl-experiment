<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\DashboardController;

Route::middleware('web')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
