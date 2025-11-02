<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\WelcomeController;

Route::middleware('web')->group(function () {
    Route::get('welcome', [WelcomeController::class, 'index'])->name('welcome.index');
});
