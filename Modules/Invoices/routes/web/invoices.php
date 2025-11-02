<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Controllers\AjaxController;
use Modules\Invoices\Controllers\CronController;
use Modules\Invoices\Controllers\InvoicesController;
use Modules\Invoices\Controllers\RecurringController;

Route::middleware('web')->group(function () {
    Route::get('invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Route::get('invoices/status', [InvoicesController::class, 'status'])->name('invoices.status');
    Route::get('invoices/archive', [InvoicesController::class, 'archive'])->name('invoices.archive');
    Route::get('invoices/download', [InvoicesController::class, 'download'])->name('invoices.download');
    Route::get('invoices/view', [InvoicesController::class, 'view'])->name('invoices.view');
    Route::get('invoices/delete', [InvoicesController::class, 'delete'])->name('invoices.delete');
    Route::get('invoices/generate-pdf', [InvoicesController::class, 'generatePdf'])->name('invoices.generate-pdf');
    Route::get('invoices/generate-xml', [InvoicesController::class, 'generateXml'])->name('invoices.generate-xml');
    Route::get('invoices/generate-sumex-pdf', [InvoicesController::class, 'generateSumexPdf'])->name('invoices.generate-sumex-pdf');
    Route::get('invoices/generate-sumex-copy', [InvoicesController::class, 'generateSumexCopy'])->name('invoices.generate-sumex-copy');
    Route::get('invoices/delete-invoice-tax', [InvoicesController::class, 'deleteInvoiceTax'])->name('invoices.delete-invoice-tax');
    Route::get('invoices/recalculate-all-invoices', [InvoicesController::class, 'recalculateAllInvoices'])->name('invoices.recalculate-all-invoices');
    Route::get('invoices', [RecurringController::class, 'index'])->name('invoices.index');
    Route::get('invoices/stop', [RecurringController::class, 'stop'])->name('invoices.stop');
    Route::get('invoices/delete', [RecurringController::class, 'delete'])->name('invoices.delete');
    Route::post('invoices/save', [AjaxController::class, 'save'])->name('invoices.save');
    Route::post('invoices/save-invoice-tax-rate', [AjaxController::class, 'saveInvoiceTaxRate'])->name('invoices.save-invoice-tax-rate');
    Route::get('invoices/delete-item', [AjaxController::class, 'deleteItem'])->name('invoices.delete-item');
    Route::get('invoices/get-item', [AjaxController::class, 'getItem'])->name('invoices.get-item');
    Route::get('invoices/modal-copy-invoice', [AjaxController::class, 'modalCopyInvoice'])->name('invoices.modal-copy-invoice');
    Route::get('invoices/copy-invoice', [AjaxController::class, 'copyInvoice'])->name('invoices.copy-invoice');
    Route::get('invoices/modal-change-user', [AjaxController::class, 'modalChangeUser'])->name('invoices.modal-change-user');
    Route::get('invoices/change-user', [AjaxController::class, 'changeUser'])->name('invoices.change-user');
    Route::get('invoices/modal-change-client', [AjaxController::class, 'modalChangeClient'])->name('invoices.modal-change-client');
    Route::get('invoices/change-client', [AjaxController::class, 'changeClient'])->name('invoices.change-client');
    Route::get('invoices/modal-create-invoice', [AjaxController::class, 'modalCreateInvoice'])->name('invoices.modal-create-invoice');
    Route::post('invoices/create', [AjaxController::class, 'create'])->name('invoices.create');
    Route::get('invoices/create-recurring', [AjaxController::class, 'createRecurring'])->name('invoices.create-recurring');
    Route::get('invoices/modal-create-recurring', [AjaxController::class, 'modalCreateRecurring'])->name('invoices.modal-create-recurring');
    Route::get('invoices/get-recur-start-date', [AjaxController::class, 'getRecurStartDate'])->name('invoices.get-recur-start-date');
    Route::get('invoices/modal-create-credit', [AjaxController::class, 'modalCreateCredit'])->name('invoices.modal-create-credit');
    Route::get('invoices/create-credit', [AjaxController::class, 'createCredit'])->name('invoices.create-credit');
    Route::get('invoices/recur', [CronController::class, 'recur'])->name('invoices.recur');
});
