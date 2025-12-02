<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Controllers\TasksAjaxController;
use Modules\Projects\Controllers\TasksController;

Route::middleware('web')->group(function () {
    Route::get('tasks/modal-task-lookups', [TasksAjaxController::class, 'modalTaskLookups'])->name('tasks.modal-task-lookups');
    Route::get('tasks/process-task-selections', [TasksAjaxController::class, 'processTaskSelections'])->name('tasks.process-task-selections');
    Route::get('tasks', [TasksController::class, 'index'])->name('tasks.index');
    Route::get('tasks/form', [TasksController::class, 'form'])->name('tasks.form');
    Route::get('tasks/delete', [TasksController::class, 'delete'])->name('tasks.delete');
});
