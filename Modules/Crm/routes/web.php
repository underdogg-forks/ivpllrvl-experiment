<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Controllers\CrmController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('crms', CrmController::class)->names('crm');
});
