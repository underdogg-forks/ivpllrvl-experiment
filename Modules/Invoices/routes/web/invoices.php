<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Controllers\InvoiceGroupsController;
use Modules\Invoices\Controllers\InvoicesAjaxController;
use Modules\Invoices\Controllers\InvoicesController;
use Modules\Invoices\Controllers\RecurringController;

/*
|--------------------------------------------------------------------------
| Invoices Web Routes
|--------------------------------------------------------------------------
*/

// Index routes
Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
Route::get('/invoices/index', [InvoicesController::class, 'index']);
Route::get('/invoices/archive', [InvoicesController::class, 'archive'])->name('invoices.archive');
Route::get('/invoices/status/all', [InvoicesController::class, 'status'])->defaults('status', 'all');
Route::get('/invoices/status/draft', [InvoicesController::class, 'status'])->defaults('status', 'draft');
Route::get('/invoices/status/overdue', [InvoicesController::class, 'status'])->defaults('status', 'overdue');
Route::get('/invoices/status/paid', [InvoicesController::class, 'status'])->defaults('status', 'paid');
Route::get('/invoices/status/sent', [InvoicesController::class, 'status'])->defaults('status', 'sent');
Route::get('/invoices/status/viewed', [InvoicesController::class, 'status'])->defaults('status', 'viewed');

// Recurring invoices
Route::get('/invoices/recurring', [RecurringController::class, 'index'])->name('invoices.recurring');
Route::get('/invoices/recurring/index', [RecurringController::class, 'index']);

// Invoice groups
Route::get('/invoice_groups/index', [InvoiceGroupsController::class, 'index'])->name('invoice_groups.index');

// View routes
Route::get('/invoices/view/{id}', [InvoicesController::class, 'view'])->name('invoices.view');

// Form routes (GET to display form)
Route::get('/invoice_groups/form', [InvoiceGroupsController::class, 'form'])->name('invoice_groups.form');
Route::get('/invoice_groups/form/{id}', [InvoiceGroupsController::class, 'form']);

// POST routes for create/update
Route::post('/invoice_groups/form', [InvoiceGroupsController::class, 'form']);
Route::post('/invoice_groups/form/{id}', [InvoiceGroupsController::class, 'form']);

// Delete routes (POST for safety)
Route::post('/invoices/delete/{id}', [InvoicesController::class, 'delete'])->name('invoices.delete');
Route::post('/invoices/recurring/stop/{id}', [RecurringController::class, 'stop'])->name('invoices.recurring.stop');
Route::post('/invoice_groups/delete/{id}', [InvoiceGroupsController::class, 'delete'])->name('invoice_groups.delete');

// AJAX routes
Route::get('/invoices/generate_pdf/{id}', [InvoicesAjaxController::class, 'generatePdf'])->name('invoices.generate_pdf');
