<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Middleware\AuthenticateUser;
use Modules\Core\Middleware\VerifyCsrfToken;
use Modules\Projects\Controllers\ProjectsController;

Route::middleware(['web', AuthenticateUser::class, VerifyCsrfToken::class])->group(function () {
    Route::get('projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('projects/form', [ProjectsController::class, 'form'])->name('projects.form');
    Route::get('projects/view', [ProjectsController::class, 'view'])->name('projects.view');
    Route::post('projects/delete', [ProjectsController::class, 'delete'])->name('projects.delete');
});
