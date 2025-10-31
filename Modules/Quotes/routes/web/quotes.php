<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotes\Controllers\QuotesAjaxController;
use Modules\Quotes\Controllers\QuotesController;

/*
|--------------------------------------------------------------------------
| Quotes Web Routes
|--------------------------------------------------------------------------
*/

// Index routes
Route::get('/quotes', [QuotesController::class, 'index'])->name('quotes.index');
Route::get('/quotes/index', [QuotesController::class, 'index']);
Route::get('/quotes/status/all', [QuotesController::class, 'status'])->defaults('status', 'all')->name('quotes.status');
Route::get('/quotes/status/approved', [QuotesController::class, 'status'])->defaults('status', 'approved');
Route::get('/quotes/status/canceled', [QuotesController::class, 'status'])->defaults('status', 'canceled');
Route::get('/quotes/status/draft', [QuotesController::class, 'status'])->defaults('status', 'draft');
Route::get('/quotes/status/rejected', [QuotesController::class, 'status'])->defaults('status', 'rejected');
Route::get('/quotes/status/sent', [QuotesController::class, 'status'])->defaults('status', 'sent');
Route::get('/quotes/status/viewed', [QuotesController::class, 'status'])->defaults('status', 'viewed');

// View routes
Route::get('/quotes/view/{quote_id}', [QuotesController::class, 'view'])->name('quotes.view');

// Delete routes (POST for safety)
Route::post('/quotes/delete/{quote_id}', [QuotesController::class, 'delete'])->name('quotes.delete');
Route::post('/quotes/cancel/{quote_id}', [QuotesController::class, 'cancel'])->name('quotes.cancel');
Route::post('/quotes/delete_tax/{quote_id}/{quote_tax_rate_id}', [QuotesController::class, 'deleteQuoteTax'])->name('quotes.delete_tax');

// Maintenance routes
Route::post('/quotes/recalculate_all', [QuotesController::class, 'recalculateAllQuotes'])->name('quotes.recalculate_all');

// AJAX routes
Route::get('/quotes/generate_pdf/{id}', [QuotesAjaxController::class, 'generatePdf'])->name('quotes.generate_pdf');
Route::post('/quotes/ajax/save', [QuotesAjaxController::class, 'save'])->name('quotes.ajax.save');
Route::post('/quotes/ajax/create', [QuotesAjaxController::class, 'create'])->name('quotes.ajax.create');
Route::post('/quotes/ajax/save-tax-rate', [QuotesAjaxController::class, 'saveQuoteTaxRate'])->name('quotes.ajax.save_tax_rate');
Route::post('/quotes/ajax/delete-item/{quoteId}', [QuotesAjaxController::class, 'deleteItem'])->name('quotes.ajax.delete_item');
Route::get('/quotes/ajax/get-item', [QuotesAjaxController::class, 'getItem'])->name('quotes.ajax.get_item');
Route::post('/quotes/ajax/copy', [QuotesAjaxController::class, 'copyQuote'])->name('quotes.ajax.copy');
Route::post('/quotes/ajax/change-user', [QuotesAjaxController::class, 'changeUser'])->name('quotes.ajax.change_user');
Route::post('/quotes/ajax/change-client', [QuotesAjaxController::class, 'changeClient'])->name('quotes.ajax.change_client');
Route::post('/quotes/ajax/quote-to-invoice', [QuotesAjaxController::class, 'quoteToInvoice'])->name('quotes.ajax.quote_to_invoice');

// Modal routes
Route::get('/quotes/modal/copy', [QuotesAjaxController::class, 'modalCopyQuote'])->name('quotes.modal.copy');
Route::get('/quotes/modal/create', [QuotesAjaxController::class, 'modalCreateQuote'])->name('quotes.modal.create');
Route::get('/quotes/modal/change-user', [QuotesAjaxController::class, 'modalChangeUser'])->name('quotes.modal.change_user');
Route::get('/quotes/modal/change-client', [QuotesAjaxController::class, 'modalChangeClient'])->name('quotes.modal.change_client');
