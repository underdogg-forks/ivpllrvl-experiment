<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Controllers\ProjectsController;
use Modules\Projects\Controllers\TasksController;

/*
|--------------------------------------------------------------------------
| Projects Web Routes (Projects & Tasks)
|--------------------------------------------------------------------------
*/

// Projects index routes
Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
Route::get('/projects/index', [ProjectsController::class, 'index']);

// Tasks index routes
Route::get('/tasks', [TasksController::class, 'index'])->name('tasks.index');
Route::get('/tasks/index', [TasksController::class, 'index']);

// View routes
Route::get('/projects/view/{project}', [ProjectsController::class, 'view'])->name('projects.view');

// Form routes (GET to display form)
Route::get('/projects/create', [ProjectsController::class, 'create'])->name('projects.create');
Route::get('/projects/{project}/edit', [ProjectsController::class, 'edit'])->name('projects.edit');
Route::get('/tasks/create', [TasksController::class, 'create'])->name('tasks.create');
Route::get('/tasks/{task}/edit', [TasksController::class, 'edit'])->name('tasks.edit');

// POST routes for create/update
Route::post('/projects', [ProjectsController::class, 'store'])->name('projects.store');
Route::put('/projects/{project}', [ProjectsController::class, 'update'])->name('projects.update');
Route::post('/tasks', [TasksController::class, 'store'])->name('tasks.store');
Route::put('/tasks/{task}', [TasksController::class, 'update'])->name('tasks.update');

// Delete routes
Route::delete('/projects/{project}', [ProjectsController::class, 'destroy'])->name('projects.destroy');
Route::delete('/tasks/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy');

// Legacy routes (for backward compatibility)
// These routes allow existing code to continue using POST-based URLs.
// New code should use the RESTful routes above with proper HTTP verbs.
// Note: POST to /form/{id} is handled by update(), POST to /delete/{id} by destroy()
Route::get('/projects/form', [ProjectsController::class, 'create']);
Route::get('/projects/form/{project}', [ProjectsController::class, 'edit']);
Route::post('/projects/form', [ProjectsController::class, 'store']);
Route::post('/projects/form/{project}', [ProjectsController::class, 'update']);
Route::post('/projects/delete/{project}', [ProjectsController::class, 'destroy']);

Route::get('/tasks/form', [TasksController::class, 'create']);
Route::get('/tasks/form/{task}', [TasksController::class, 'edit']);
Route::post('/tasks/form', [TasksController::class, 'store']);
Route::post('/tasks/form/{task}', [TasksController::class, 'update']);
Route::post('/tasks/delete/{task}', [TasksController::class, 'destroy']);
