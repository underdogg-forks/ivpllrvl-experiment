<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Controllers\FilterAjaxController;

Route::middleware('web')->group(function () {
    Route::get('filter/filter-invoices', [FilterAjaxController::class, 'filterInvoices'])->name('filter.filter-invoices');
    Route::get('filter/filter-quotes', [FilterAjaxController::class, 'filterQuotes'])->name('filter.filter-quotes');
    Route::get('filter/filter-clients', [FilterAjaxController::class, 'filterClients'])->name('filter.filter-clients');
    Route::get('filter/filter-custom-fields', [FilterAjaxController::class, 'filterCustomFields'])->name('filter.filter-custom-fields');
    Route::get('filter/filter-custom-values', [FilterAjaxController::class, 'filterCustomValues'])->name('filter.filter-custom-values');
    Route::get('filter/filter-custom-values-field', [FilterAjaxController::class, 'filterCustomValuesField'])->name('filter.filter-custom-values-field');
    Route::get('filter/filter-projects', [FilterAjaxController::class, 'filterProjects'])->name('filter.filter-projects');
    Route::get('filter/filter-tasks', [FilterAjaxController::class, 'filterTasks'])->name('filter.filter-tasks');
    Route::get('filter/filter-products', [FilterAjaxController::class, 'filterProducts'])->name('filter.filter-products');
    Route::get('filter/filter-users', [FilterAjaxController::class, 'filterUsers'])->name('filter.filter-users');
    Route::get('filter/filter-families', [FilterAjaxController::class, 'filterFamilies'])->name('filter.filter-families');
    Route::get('filter/filter-invoices-recuring', [FilterAjaxController::class, 'filterInvoicesRecuring'])->name('filter.filter-invoices-recuring');
    Route::get('filter/filter-online-logs', [FilterAjaxController::class, 'filterOnlineLogs'])->name('filter.filter-online-logs');
    Route::get('filter/filter-archives', [FilterAjaxController::class, 'filterArchives'])->name('filter.filter-archives');
    Route::get('filter/filter-payments', [FilterAjaxController::class, 'filterPayments'])->name('filter.filter-payments');
});
