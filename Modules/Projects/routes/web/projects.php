<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Controllers\ProjectsController;

Route::middleware('web')->group(function () {
    Route::get('projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('projects/form/{project_id?}', [ProjectsController::class, 'form'])->name('projects.form');
    Route::get('projects/view/{project_id?}', [ProjectsController::class, 'view'])->name('projects.view');
    Route::get('projects/delete/{project_id}', [ProjectsController::class, 'delete'])->name('projects.delete');
});
