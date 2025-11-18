<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Middleware\AuthenticateUser;
use Modules\Core\Middleware\VerifyCsrfToken;
use Modules\Projects\Controllers\TasksAjaxController;
use Modules\Projects\Controllers\TasksController;

Route::middleware(['web', AuthenticateUser::class, VerifyCsrfToken::class])->group(function () {
    Route::get('tasks/modal-task-lookups', [TasksAjaxController::class, 'modalTaskLookups'])->name('tasks.modal-task-lookups');
    Route::get('tasks/process-task-selections', [TasksAjaxController::class, 'processTaskSelections'])->name('tasks.process-task-selections');
    Route::get('tasks', [TasksController::class, 'index'])->name('tasks.index');
    Route::get('tasks/form', [TasksController::class, 'form'])->name('tasks.form');
    Route::post('tasks/delete', [TasksController::class, 'delete'])->name('tasks.delete');
});
