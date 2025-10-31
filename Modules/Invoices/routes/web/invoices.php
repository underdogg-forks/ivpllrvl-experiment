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
Route::get('/invoices/view/{invoiceId}', [InvoicesController::class, 'view'])->name('invoices.view');

// Form routes (GET to display form)
Route::get('/invoice_groups/form', [InvoiceGroupsController::class, 'form'])->name('invoice_groups.form');
Route::get('/invoice_groups/form/{id}', [InvoiceGroupsController::class, 'form']);

// POST routes for create/update
Route::post('/invoice_groups/form', [InvoiceGroupsController::class, 'form']);
Route::post('/invoice_groups/form/{id}', [InvoiceGroupsController::class, 'form']);

// Delete routes (POST for safety)
Route::post('/invoices/delete/{invoiceId}', [InvoicesController::class, 'delete'])->name('invoices.delete');
Route::post('/invoices/recurring/stop/{id}', [RecurringController::class, 'stop'])->name('invoices.recurring.stop');
Route::post('/invoices/recurring/delete/{id}', [RecurringController::class, 'delete'])->name('invoices.recurring.delete');
Route::post('/invoice_groups/delete/{id}', [InvoiceGroupsController::class, 'delete'])->name('invoice_groups.delete');

// Management routes
Route::post('/invoices/delete-tax/{invoiceId}/{taxRateId}', [InvoicesController::class, 'deleteInvoiceTax'])->name('invoices.delete_tax');
Route::post('/invoices/recalculate-all', [InvoicesController::class, 'recalculateAllInvoices'])->name('invoices.recalculate_all');
Route::get('/invoices/download/{filename}', [InvoicesController::class, 'download'])->name('invoices.download');

// AJAX routes
Route::get('/invoices/generate_pdf/{id}', [InvoicesAjaxController::class, 'generatePdf'])->name('invoices.generate_pdf');
Route::post('/invoices/ajax/save', [InvoicesAjaxController::class, 'save'])->name('invoices.ajax.save');
Route::post('/invoices/ajax/create', [InvoicesAjaxController::class, 'create'])->name('invoices.ajax.create');
Route::post('/invoices/ajax/save-tax-rate', [InvoicesAjaxController::class, 'saveInvoiceTaxRate'])->name('invoices.ajax.save_tax_rate');
Route::post('/invoices/ajax/delete-item/{invoiceId}', [InvoicesAjaxController::class, 'deleteItem'])->name('invoices.ajax.delete_item');
Route::get('/invoices/ajax/get-item', [InvoicesAjaxController::class, 'getItem'])->name('invoices.ajax.get_item');
Route::post('/invoices/ajax/copy', [InvoicesAjaxController::class, 'copyInvoice'])->name('invoices.ajax.copy');
Route::post('/invoices/ajax/change-user', [InvoicesAjaxController::class, 'changeUser'])->name('invoices.ajax.change_user');
Route::post('/invoices/ajax/change-client', [InvoicesAjaxController::class, 'changeClient'])->name('invoices.ajax.change_client');
Route::post('/invoices/ajax/create-recurring', [InvoicesAjaxController::class, 'createRecurring'])->name('invoices.ajax.create_recurring');
Route::post('/invoices/ajax/create-credit', [InvoicesAjaxController::class, 'createCredit'])->name('invoices.ajax.create_credit');
Route::get('/invoices/ajax/recur-start-date', [InvoicesAjaxController::class, 'getRecurStartDate'])->name('invoices.ajax.recur_start_date');

// Modal routes
Route::get('/invoices/modal/copy', [InvoicesAjaxController::class, 'modalCopyInvoice'])->name('invoices.modal.copy');
Route::get('/invoices/modal/create', [InvoicesAjaxController::class, 'modalCreateInvoice'])->name('invoices.modal.create');
Route::get('/invoices/modal/change-user', [InvoicesAjaxController::class, 'modalChangeUser'])->name('invoices.modal.change_user');
Route::get('/invoices/modal/change-client', [InvoicesAjaxController::class, 'modalChangeClient'])->name('invoices.modal.change_client');
Route::get('/invoices/modal/create-recurring', [InvoicesAjaxController::class, 'modalCreateRecurring'])->name('invoices.modal.create_recurring');
Route::get('/invoices/modal/create-credit', [InvoicesAjaxController::class, 'modalCreateCredit'])->name('invoices.modal.create_credit');
