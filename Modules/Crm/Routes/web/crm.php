<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Controllers\ClientsController;
use Modules\Crm\Controllers\UserClientsController;
use Modules\Crm\Controllers\ProjectsController;
use Modules\Crm\Controllers\TasksController;
use Modules\Crm\Controllers\GuestController;
use Modules\Crm\Controllers\GetController;
use Modules\Crm\Controllers\ViewController;

/*
|--------------------------------------------------------------------------
| CRM Web Routes (Clients & Projects)
|--------------------------------------------------------------------------
*/

// Clients index routes
Route::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
Route::get('/clients/index', [ClientsController::class, 'index']);
Route::get('/clients/status/active', [ClientsController::class, 'status'])->defaults('status', 'active');
Route::get('/clients/status/all', [ClientsController::class, 'status'])->defaults('status', 'all');
Route::get('/clients/status/inactive', [ClientsController::class, 'status'])->defaults('status', 'inactive');
Route::get('/user_clients/index', [UserClientsController::class, 'index'])->name('user_clients.index');

// Projects & Tasks index routes
Route::get('/tasks/index', [TasksController::class, 'index'])->name('tasks.index');
Route::get('/projects/index', [ProjectsController::class, 'index'])->name('projects.index');

// View routes
Route::get('/clients/view/{id}', [ClientsController::class, 'view'])->name('clients.view');
Route::get('/clients/view/{id}/invoices', [ClientsController::class, 'viewInvoices']);
Route::get('/projects/view/{id}', [ProjectsController::class, 'view'])->name('projects.view');

// Guest portal routes
Route::get('/guest/view/{id}', [GuestController::class, 'view'])->name('guest.view');
Route::get('/guest/invoice/{id}', [GetController::class, 'invoice'])->name('guest.invoice');
Route::get('/guest/quote/{id}', [GetController::class, 'quote'])->name('guest.quote');

// Form routes (GET to display form)
Route::get('/clients/form', [ClientsController::class, 'form'])->name('clients.form');
Route::get('/clients/form/{id}', [ClientsController::class, 'form']);
Route::get('/user_clients/form', [UserClientsController::class, 'form'])->name('user_clients.form');
Route::get('/user_clients/form/{id}', [UserClientsController::class, 'form']);
Route::get('/tasks/form', [TasksController::class, 'form'])->name('tasks.form');
Route::get('/tasks/form/{id}', [TasksController::class, 'form']);
Route::get('/projects/form', [ProjectsController::class, 'form'])->name('projects.form');
Route::get('/projects/form/{id}', [ProjectsController::class, 'form']);

// POST routes for create/update
Route::post('/clients/form', [ClientsController::class, 'form']);
Route::post('/clients/form/{id}', [ClientsController::class, 'form']);
Route::post('/user_clients/form', [UserClientsController::class, 'form']);
Route::post('/user_clients/form/{id}', [UserClientsController::class, 'form']);
Route::post('/tasks/form', [TasksController::class, 'form']);
Route::post('/tasks/form/{id}', [TasksController::class, 'form']);
Route::post('/projects/form', [ProjectsController::class, 'form']);
Route::post('/projects/form/{id}', [ProjectsController::class, 'form']);

// Delete routes (POST for safety)
Route::post('/clients/delete/{id}', [ClientsController::class, 'delete'])->name('clients.delete');
Route::post('/clients/remove/{id}', [ClientsController::class, 'delete']); // Alias
Route::post('/user_clients/delete/{id}', [UserClientsController::class, 'delete'])->name('user_clients.delete');
Route::post('/tasks/delete/{id}', [TasksController::class, 'delete'])->name('tasks.delete');
Route::post('/projects/delete/{id}', [ProjectsController::class, 'delete'])->name('projects.delete');
