<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\CoreController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('cores', CoreController::class)->names('core');
});
