<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CoreController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cores', CoreController::class)->names('core');
});
