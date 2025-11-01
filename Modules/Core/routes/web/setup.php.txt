<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\SetupController;

Route::middleware('web')->group(function () {
    Route::get('setup', [SetupController::class, 'index'])->name('setup.index');
    Route::get('setup/language', [SetupController::class, 'language'])->name('setup.language');
    Route::get('setup/prerequisites', [SetupController::class, 'prerequisites'])->name('setup.prerequisites');
    Route::get('setup/configure-database', [SetupController::class, 'configureDatabase'])->name('setup.configure-database');
    Route::get('setup/install-tables', [SetupController::class, 'installTables'])->name('setup.install-tables');
    Route::get('setup/upgrade-tables', [SetupController::class, 'upgradeTables'])->name('setup.upgrade-tables');
    Route::get('setup/create-user', [SetupController::class, 'createUser'])->name('setup.create-user');
    Route::get('setup/calculation-info', [SetupController::class, 'calculationInfo'])->name('setup.calculation-info');
    Route::get('setup/complete', [SetupController::class, 'complete'])->name('setup.complete');
});
