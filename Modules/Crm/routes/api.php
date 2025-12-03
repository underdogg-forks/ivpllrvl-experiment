<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Controllers\CrmController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('crms', CrmController::class)->names('crm');
});
