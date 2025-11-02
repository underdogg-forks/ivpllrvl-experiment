<?php

use Illuminate\Support\Facades\Route;
use Modules\Invoices\app\Http\Controllers\InvoiceGroupsController;

Route::middleware('web')->group(function () {
    Route::get('invoice-groups', [InvoiceGroupsController::class, 'index'])->name('invoice-groups.index');
    Route::get('invoice-groups/form', [InvoiceGroupsController::class, 'form'])->name('invoice-groups.form');
    Route::get('invoice-groups/delete', [InvoiceGroupsController::class, 'delete'])->name('invoice-groups.delete');
});
