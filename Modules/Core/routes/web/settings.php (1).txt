<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\SettingsAjaxController;
use Modules\Core\Controllers\SettingsController;
use Modules\Core\Controllers\VersionsController;

Route::middleware('web')->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/remove-logo', [SettingsController::class, 'removeLogo'])->name('settings.remove-logo');
    Route::get('settings/get-cron-key', [SettingsAjaxController::class, 'getCronKey'])->name('settings.get-cron-key');
    Route::get('settings', [VersionsController::class, 'index'])->name('settings.index');
});
