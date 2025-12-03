<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Controllers\InvoiceGroupsController;

Route::middleware('web')->group(function () {
    Route::get('invoice-groups', [InvoiceGroupsController::class, 'index'])->name('invoice-groups.index');
    Route::get('invoice-groups/form/{invoice_group_id?}', [InvoiceGroupsController::class, 'form'])->name('invoice-groups.form');
    Route::post('invoice-groups/delete/{invoice_group_id}', [InvoiceGroupsController::class, 'delete'])->name('invoice-groups.delete');
});
