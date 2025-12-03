<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Controllers\ProjectsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('projects', ProjectsController::class)->names('projects');
});
