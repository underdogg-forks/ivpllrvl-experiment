<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Controllers\ClientsController;
use Modules\Crm\Controllers\GetController;
use Modules\Crm\Controllers\GuestController;
use Modules\Crm\Controllers\UserClientsController;

/*
|--------------------------------------------------------------------------
| CRM Web Routes (Clients)
|--------------------------------------------------------------------------
*/

// Clients index routes
Route::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
Route::get('/clients/index', [ClientsController::class, 'index']);
Route::get('/clients/status/active', [ClientsController::class, 'status'])->defaults('status', 'active');
Route::get('/clients/status/all', [ClientsController::class, 'status'])->defaults('status', 'all');
Route::get('/clients/status/inactive', [ClientsController::class, 'status'])->defaults('status', 'inactive');
Route::get('/user_clients/index', [UserClientsController::class, 'index'])->name('user_clients.index');

// View routes
Route::get('/clients/view/{id}', [ClientsController::class, 'view'])->name('clients.view');
Route::get('/clients/view/{id}/invoices', [ClientsController::class, 'viewInvoices']);

// Guest portal routes
Route::get('/guest/view/{id}', [GuestController::class, 'view'])->name('guest.view');
Route::get('/guest/invoice/{id}', [GetController::class, 'invoice'])->name('guest.invoice');
Route::get('/guest/quote/{id}', [GetController::class, 'quote'])->name('guest.quote');

// Form routes (GET to display form)
Route::get('/clients/form', [ClientsController::class, 'form'])->name('clients.form');
Route::get('/clients/form/{id}', [ClientsController::class, 'form']);
Route::get('/user_clients/form', [UserClientsController::class, 'form'])->name('user_clients.form');
Route::get('/user_clients/form/{id}', [UserClientsController::class, 'form']);

// POST routes for create/update
Route::post('/clients/form', [ClientsController::class, 'form']);
Route::post('/clients/form/{id}', [ClientsController::class, 'form']);
Route::post('/user_clients/form', [UserClientsController::class, 'form']);
Route::post('/user_clients/form/{id}', [UserClientsController::class, 'form']);

// Delete routes (POST for safety)
Route::post('/clients/delete/{id}', [ClientsController::class, 'delete'])->name('clients.delete');
Route::post('/clients/remove/{id}', [ClientsController::class, 'delete']); // Alias
Route::post('/user_clients/delete/{id}', [UserClientsController::class, 'delete'])->name('user_clients.delete');
