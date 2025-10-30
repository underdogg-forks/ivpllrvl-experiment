<?php

use Illuminate\Support\Facades\Route;
use Modules\Quotes\Controllers\QuotesController;
use Modules\Quotes\Controllers\QuotesAjaxController;

/*
|--------------------------------------------------------------------------
| Quotes Web Routes
|--------------------------------------------------------------------------
*/

// Index routes
Route::get('/quotes', [QuotesController::class, 'index'])->name('quotes.index');
Route::get('/quotes/index', [QuotesController::class, 'index']);
Route::get('/quotes/status/all', [QuotesController::class, 'status'])->defaults('status', 'all');
Route::get('/quotes/status/approved', [QuotesController::class, 'status'])->defaults('status', 'approved');
Route::get('/quotes/status/canceled', [QuotesController::class, 'status'])->defaults('status', 'canceled');
Route::get('/quotes/status/draft', [QuotesController::class, 'status'])->defaults('status', 'draft');
Route::get('/quotes/status/rejected', [QuotesController::class, 'status'])->defaults('status', 'rejected');
Route::get('/quotes/status/sent', [QuotesController::class, 'status'])->defaults('status', 'sent');
Route::get('/quotes/status/viewed', [QuotesController::class, 'status'])->defaults('status', 'viewed');

// View routes
Route::get('/quotes/view/{id}', [QuotesController::class, 'view'])->name('quotes.view');

// Delete routes (POST for safety)
Route::post('/quotes/delete/{id}', [QuotesController::class, 'delete'])->name('quotes.delete');
Route::post('/quotes/cancel/{id}', [QuotesController::class, 'cancel'])->name('quotes.cancel');

// AJAX routes
Route::get('/quotes/generate_pdf/{id}', [QuotesAjaxController::class, 'generatePdf'])->name('quotes.generate_pdf');
