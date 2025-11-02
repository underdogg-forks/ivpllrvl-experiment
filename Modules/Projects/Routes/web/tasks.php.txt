<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\app\Http\Controllers\AjaxController;
use Modules\Projects\app\Http\Controllers\TasksController;

Route::middleware('web')->group(function () {
    Route::get('tasks/modal-task-lookups', [AjaxController::class, 'modalTaskLookups'])->name('tasks.modal-task-lookups');
    Route::get('tasks/process-task-selections', [AjaxController::class, 'processTaskSelections'])->name('tasks.process-task-selections');
    Route::get('tasks', [TasksController::class, 'index'])->name('tasks.index');
    Route::get('tasks/form', [TasksController::class, 'form'])->name('tasks.form');
    Route::get('tasks/delete', [TasksController::class, 'delete'])->name('tasks.delete');
});
